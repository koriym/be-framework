<?php

declare(strict_types=1);

namespace Be\Framework\SemanticVariable;

use Be\Framework\BecomingArguments;
use Be\Framework\SemanticTag\Adult;
use Be\Framework\SemanticTag\Premium;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;
use ReflectionMethod;

final class SemanticParamsTest extends TestCase
{
    private SemanticValidator $validator;

    protected function setUp(): void
    {
        $injector = new Injector();
        $becomingArguments = new BecomingArguments($injector);
        $this->validator = new SemanticValidator($becomingArguments, 'Be\\Framework\\SemanticVariables');
    }

    public function testValidateWithAllValidValues(): void
    {
        $method = new ReflectionMethod(TestServiceForSemanticParams::class, 'processUser');
        $params = new SemanticParams($method, $this->validator);

        $values = [
            'user_age' => 25,
            'name' => 'John Doe',
            'product_price' => 150.0,
        ];

        $errors = $params->validate($values);

        $this->assertInstanceOf(NullErrors::class, $errors);
        $this->assertFalse($errors->hasErrors());
    }

    public function testValidateWithInvalidSemanticValues(): void
    {
        $method = new ReflectionMethod(TestServiceForSemanticParams::class, 'processUser');
        $params = new SemanticParams($method, $this->validator);

        $values = [
            'user_age' => 15,        // Invalid: too young for Adult
            'name' => 'John Doe',
            'product_price' => 50.0,  // Invalid: too cheap for Premium
        ];

        $errors = $params->validate($values);

        $this->assertTrue($errors->hasErrors());
        $messages = $errors->getMessages();
        $this->assertCount(2, $messages);
        $this->assertStringContainsString('Adult age must be at least 18: 15', $messages[0]);
        $this->assertStringContainsString('Premium price must be at least 100: 50', $messages[1]);
    }

    public function testValidateWithMixedValidAndInvalidValues(): void
    {
        $method = new ReflectionMethod(TestServiceForSemanticParams::class, 'processUser');
        $params = new SemanticParams($method, $this->validator);

        $values = [
            'user_age' => 25,        // Valid
            'name' => 'John Doe',
            'product_price' => 50.0,  // Invalid: too cheap for Premium
        ];

        $errors = $params->validate($values);

        $this->assertTrue($errors->hasErrors());
        $messages = $errors->getMessages();
        $this->assertCount(1, $messages);
        $this->assertStringContainsString('Premium price must be at least 100: 50', $messages[0]);
    }

    public function testValidateWithPartialValues(): void
    {
        $method = new ReflectionMethod(TestServiceForSemanticParams::class, 'processUser');
        $params = new SemanticParams($method, $this->validator);

        $values = [
            'user_age' => 25,
            // name and product_price are missing
        ];

        $errors = $params->validate($values);

        $this->assertInstanceOf(NullErrors::class, $errors);
        $this->assertFalse($errors->hasErrors());
    }

    public function testValidateMethodWithNoSemanticParameters(): void
    {
        $method = new ReflectionMethod(TestServiceForSemanticParams::class, 'simpleMethod');
        $params = new SemanticParams($method, $this->validator);

        $values = ['message' => 'Hello World'];
        $errors = $params->validate($values);

        $this->assertInstanceOf(NullErrors::class, $errors);
        $this->assertFalse($errors->hasErrors());
    }

    public function testValidateWithEmptyValues(): void
    {
        $method = new ReflectionMethod(TestServiceForSemanticParams::class, 'processUser');
        $params = new SemanticParams($method, $this->validator);

        $errors = $params->validate([]);

        $this->assertInstanceOf(NullErrors::class, $errors);
        $this->assertFalse($errors->hasErrors());
    }

    public function testReusabilityWithDifferentValues(): void
    {
        $method = new ReflectionMethod(TestServiceForSemanticParams::class, 'processUser');
        $params = new SemanticParams($method, $this->validator);

        // First validation - valid
        $validValues = ['user_age' => 25, 'name' => 'Alice', 'product_price' => 200.0];
        $errors1 = $params->validate($validValues);
        $this->assertFalse($errors1->hasErrors());

        // Second validation - invalid
        $invalidValues = ['user_age' => 16, 'name' => 'Bob', 'product_price' => 80.0];
        $errors2 = $params->validate($invalidValues);
        $this->assertTrue($errors2->hasErrors());

        // Third validation - mixed
        $mixedValues = ['user_age' => 30, 'name' => 'Charlie', 'product_price' => 50.0];
        $errors3 = $params->validate($mixedValues);
        $this->assertTrue($errors3->hasErrors());
        $this->assertCount(1, $errors3->getMessages()); // Only product_price should fail
    }

    public function testConstructionFromReflectionMethod(): void
    {
        $method = new ReflectionMethod(TestServiceForSemanticParams::class, 'processUser');

        $params = new SemanticParams($method, $this->validator);

        $this->assertInstanceOf(SemanticParams::class, $params);

        // Verify it can be used immediately
        $errors = $params->validate(['user_age' => 25, 'product_price' => 150.0]);
        $this->assertFalse($errors->hasErrors());
    }
}

/**
 * Test class with various parameter configurations
 */
final class TestServiceForSemanticParams
{
    public function processUser(
        #[Adult]
        int $user_age,
        string $name,
        #[Premium]
        float $product_price,
    ): void {
    }

    public function simpleMethod(string $message): void
    {
    }
}
