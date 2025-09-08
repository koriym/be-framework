<?php

declare(strict_types=1);

namespace Be\Framework\Tests\SemanticVariable;

use Be\Framework\SemanticVariable\SemanticValidator;
use Be\Framework\Tests\Fake\TestPerson;
use Be\Framework\Tests\Fake\TestPersonConstructor;
use Be\Framework\Tests\Fake\TestPersonWithInject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Test for SemanticValidator::validateProps method
 */
final class SemanticValidatorPropsTest extends TestCase
{
    private SemanticValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new SemanticValidator('Be\\Framework\\Tests\\Fake\\Ontology');
    }

    public function testValidatePropsWithValidObject(): void
    {
        // Create object with valid properties
        $person = new TestPerson('John Doe', 25);

        // Get constructor to validate against
        $reflection = new ReflectionClass(TestPersonConstructor::class);
        $constructor = $reflection->getConstructor();

        // Validate properties
        $errors = $this->validator->validateProps($constructor, $person);

        $this->assertFalse($errors->hasErrors(), 'Valid object should pass validation');
    }

    public function testValidatePropsWithInvalidAge(): void
    {
        // Create object with invalid age (negative)
        $person = new TestPerson('John Doe', -5);

        // Get constructor to validate against
        $reflection = new ReflectionClass(TestPersonConstructor::class);
        $constructor = $reflection->getConstructor();

        // Validate properties
        $errors = $this->validator->validateProps($constructor, $person);

        // Should have errors for invalid age
        $this->assertTrue($errors->hasErrors(), 'Invalid age should cause validation errors');
    }

    public function testValidatePropsWithEmptyName(): void
    {
        // Create object with empty name
        $person = new TestPerson('', 25);

        // Get constructor to validate against
        $reflection = new ReflectionClass(TestPersonConstructor::class);
        $constructor = $reflection->getConstructor();

        // Validate properties
        $errors = $this->validator->validateProps($constructor, $person);

        // Should have errors for empty name
        $this->assertTrue($errors->hasErrors(), 'Empty name should cause validation errors');
    }

    public function testValidatePropsSkipsMissingProperties(): void
    {
        // Create object with only some properties
        $partialObject = new class {
            public string $name = 'John Doe';
            // Missing age property
        };

        // Get constructor to validate against
        $reflection = new ReflectionClass(TestPersonConstructor::class);
        $constructor = $reflection->getConstructor();

        // Validate properties - should not fail for missing properties
        $errors = $this->validator->validateProps($constructor, $partialObject);

        $this->assertFalse($errors->hasErrors(), 'Missing properties should be skipped, not cause errors');
    }

    public function testValidatePropsSkipsInjectParameters(): void
    {
        // Create object
        $person = new TestPerson('John Doe', 25);

        // Get constructor with inject parameters
        $reflection = new ReflectionClass(TestPersonWithInject::class);
        $constructor = $reflection->getConstructor();

        // Validate properties - should skip inject parameters
        $errors = $this->validator->validateProps($constructor, $person);

        $this->assertFalse($errors->hasErrors(), 'Inject parameters should be skipped');
    }
}
