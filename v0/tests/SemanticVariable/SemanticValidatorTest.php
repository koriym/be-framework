<?php

declare(strict_types=1);

namespace Be\Framework\SemanticVariable;

use Be\Framework\BecomingArguments;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

final class SemanticValidatorTest extends TestCase
{
    private SemanticValidator $validator;

    protected function setUp(): void
    {
        $injector = new Injector();
        $becomingArguments = new BecomingArguments($injector);
        $this->validator = new SemanticValidator($becomingArguments);
    }

    public function testValidEmailReturnsNoErrors(): void
    {
        $errors = $this->validator->validate('email', 'john@example.com');

        $this->assertInstanceOf(NullErrors::class, $errors);
        $this->assertFalse($errors->hasErrors());
    }

    public function testInvalidEmailReturnsErrors(): void
    {
        $errors = $this->validator->validate('email', 'invalid-email');

        $this->assertTrue($errors->hasErrors());
        $this->assertGreaterThan(0, $errors->count());
    }

    public function testValidAgeReturnsNoErrors(): void
    {
        $errors = $this->validator->validate('age', 25);

        $this->assertInstanceOf(NullErrors::class, $errors);
    }

    public function testNegativeAgeReturnsError(): void
    {
        $errors = $this->validator->validate('age', -5);

        $this->assertTrue($errors->hasErrors());
    }

    public function testValidPriceReturnsNoErrors(): void
    {
        $errors = $this->validator->validate('price', 19.99);

        $this->assertInstanceOf(NullErrors::class, $errors);
    }

    public function testNegativePriceReturnsError(): void
    {
        $errors = $this->validator->validate('price', -10.50);

        $this->assertTrue($errors->hasErrors());
    }

    public function testValidDiceReturnsNoErrors(): void
    {
        $errors = $this->validator->validate('dice', 4);

        $this->assertInstanceOf(NullErrors::class, $errors);
    }

    public function testInvalidDiceReturnsError(): void
    {
        $errors = $this->validator->validate('dice', 7);

        $this->assertTrue($errors->hasErrors());
    }

    public function testEmailConfirmationMatchReturnsNoErrors(): void
    {
        $errors = $this->validator->validate('email', 'john@example.com', 'john@example.com');

        $this->assertInstanceOf(NullErrors::class, $errors);
    }

    public function testEmailConfirmationMismatchReturnsError(): void
    {
        $errors = $this->validator->validate('email', 'john@example.com', 'jane@example.com');

        $this->assertTrue($errors->hasErrors());
    }

    public function testNonExistentSemanticVariableReturnsNoErrors(): void
    {
        $errors = $this->validator->validate('nonexistent_variable', 'some value');

        $this->assertInstanceOf(NullErrors::class, $errors);
        $this->assertFalse($errors->hasErrors());
    }

    public function testValidateObject(): void
    {
        $object = new class {
            public string $email = 'john@example.com';
            public int $age = 25;
        };

        $errors = $this->validator->validateObject($object);

        $this->assertInstanceOf(NullErrors::class, $errors);
        $this->assertFalse($errors->hasErrors());
    }

    public function testValidateObjectWithErrors(): void
    {
        $object = new class {
            public string $email = 'invalid-email';
            public int $age = -5;
        };

        $errors = $this->validator->validateObject($object);

        $this->assertTrue($errors->hasErrors());
        $this->assertGreaterThan(0, $errors->count());
    }

    public function testCustomNamespace(): void
    {
        $injector = new Injector();
        $becomingArguments = new BecomingArguments($injector);
        $validator = new SemanticValidator($becomingArguments, 'NonExistent\\Namespace');

        $errors = $validator->validate('email', 'test@example.com');

        $this->assertInstanceOf(NullErrors::class, $errors);
        $this->assertFalse($errors->hasErrors());
    }

    public function testNoMatchingValidationMethods(): void
    {
        // This tests the case where semantic class exists but no validation methods match
        // The class has validation methods but none match the argument count provided
        $errors = $this->validator->validate('no_matching_method', 'single_arg');

        $this->assertInstanceOf(NullErrors::class, $errors);
        $this->assertFalse($errors->hasErrors());
    }
}
