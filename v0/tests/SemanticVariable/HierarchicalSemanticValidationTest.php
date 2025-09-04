<?php

declare(strict_types=1);

namespace Be\Framework\SemanticVariable;

use Be\Framework\BecomingArguments;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

/**
 * Test hierarchical semantic validation with parameter attributes
 *
 * Demonstrates the beautiful pattern:
 * - Base contract: Variable name implies basic validation ($age)
 * - Additional constraint: Attribute narrows the meaning (#[Teen] $age)
 */
final class HierarchicalSemanticValidationTest extends TestCase
{
    private SemanticValidator $validator;

    protected function setUp(): void
    {
        $injector = new Injector();
        $nullValidator = new NullValidator();
        $becomingArguments = new BecomingArguments($injector, $nullValidator);
        $this->validator = new SemanticValidator($becomingArguments, 'Be\\Framework\\SemanticVariables');
    }

    public function testBasicAgeValidation(): void
    {
        // Basic age validation without attributes
        $errors = $this->validator->validate('age', [], 25);

        $this->assertFalse($errors->hasErrors(), 'Valid age should pass basic validation');
    }

    public function testBasicAgeValidationFailure(): void
    {
        // Invalid age should fail basic validation
        $errors = $this->validator->validate('age', -5);

        $this->assertTrue($errors->hasErrors());
        $this->assertStringContainsString('Age cannot be negative', $errors->getMessages()[0]);
    }

    public function testTeenAgeValidationSuccess(): void
    {
        // #[Teen] attribute should trigger teen-specific validation
        $errors = $this->validator->validateWithAttributes('age', ['Teen'], 16);

        $this->assertFalse($errors->hasErrors(), 'Valid teen age should pass validation');
    }

    public function testTeenAgeValidationTooYoung(): void
    {
        // Age too young for teen validation
        $errors = $this->validator->validateWithAttributes('age', ['Teen'], 10);

        $this->assertTrue($errors->hasErrors());
        $this->assertStringContainsString('Teen age must be at least 13', $errors->getMessages()[0]);
    }

    public function testTeenAgeValidationTooOld(): void
    {
        // Age too old for teen validation
        $errors = $this->validator->validateWithAttributes('age', ['Teen'], 25);

        $this->assertTrue($errors->hasErrors());
        $this->assertStringContainsString('Teen age must be at most 19', $errors->getMessages()[0]);
    }

    public function testTeenAgeValidationInvalidAge(): void
    {
        // Invalid age should fail basic validation within teen validation
        $errors = $this->validator->validateWithAttributes('age', ['Teen'], -5);

        $this->assertTrue($errors->hasErrors());
        $this->assertStringContainsString('Age cannot be negative', $errors->getMessages()[0]);
    }

    public function testHierarchicalSemanticMeaning(): void
    {
        // Demonstrate the beautiful hierarchical pattern

        // Base: $age → validates basic age (0-120)
        $basicValid = $this->validator->validate('age', 25);
        $this->assertFalse($basicValid->hasErrors(), 'Basic age validation');

        // Constrained: #[Teen] $age → validates teen age (13-19)
        $teenValid = $this->validator->validateWithAttributes('age', ['Teen'], 16);
        $this->assertFalse($teenValid->hasErrors(), 'Teen age validation');

        // The beauty: same variable name, different semantic meaning through attributes
        $adultInTeen = $this->validator->validateWithAttributes('age', ['Teen'], 25);
        $this->assertTrue($adultInTeen->hasErrors(), 'Adult age fails teen validation');
    }

    public function testRealWorldExample(): void
    {
        // Realistic usage scenario: movie ticket sale with age restrictions

        // Child ticket (under 13) - would fail teen validation
        $childAge = $this->validator->validateWithAttributes('age', ['Teen'], 10);
        $this->assertTrue($childAge->hasErrors());

        // Teen ticket (13-19) - perfect match
        $teenAge = $this->validator->validateWithAttributes('age', ['Teen'], 16);
        $this->assertFalse($teenAge->hasErrors());

        // Adult ticket (20+) - would fail teen validation
        $adultAge = $this->validator->validateWithAttributes('age', ['Teen'], 25);
        $this->assertTrue($adultAge->hasErrors());
    }
}
