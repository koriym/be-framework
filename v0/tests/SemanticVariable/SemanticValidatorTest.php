<?php

declare(strict_types=1);

namespace Be\Framework\SemanticVariable;

use Be\Framework\Exception\SemanticVariableException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use TypeError;

final class SemanticValidatorTest extends TestCase
{
    private SemanticValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new SemanticValidator('MyVendor\\MyApp\\SemanticVariables');
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

    public function testValidateAndThrowWithValidData(): void
    {
        $this->validator->validateAndThrow('email', 'john@example.com');

        // If no exception is thrown, the test passes
        $this->expectNotToPerformAssertions();
    }

    public function testValidateAndThrowWithInvalidData(): void
    {
        $this->expectException(SemanticVariableException::class);

        $this->validator->validateAndThrow('email', 'invalid-email');
    }

    public function testValidateAndThrowPreservesErrorDetails(): void
    {
        try {
            $this->validator->validateAndThrow('email', 'invalid-email');
            $this->fail('Expected SemanticVariableException to be thrown');
        } catch (SemanticVariableException $e) {
            // Verify that the exception preserves the original errors
            $errors = $e->getErrors();
            $this->assertTrue($errors->hasErrors());
            $this->assertGreaterThan(0, $errors->count());

            // Verify the exception message contains validation details
            $this->assertNotEmpty($e->getMessage());
        }
    }

    public function testCustomNamespace(): void
    {
        $validator = new SemanticValidator('NonExistent\\Namespace');

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

    public function testValidateWithAttributesNonExistentSemanticTag(): void
    {
        // This tests the branch where isSemanticTagClass returns false for non-existent class
        $errors = $this->validator->validateWithAttributes('email', ['NonExistentTag'], 'test@example.com');

        // Should still validate with base validation since the non-existent tag is ignored
        $this->assertInstanceOf(NullErrors::class, $errors);
        $this->assertFalse($errors->hasErrors());
    }

    public function testIsSemanticTagClassWithNonExistentClass(): void
    {
        // Test the branch in isSemanticTagClass where class_exists returns false
        // This is tested indirectly through validateWithAttributes with non-existent tag
        $errors = $this->validator->validateWithAttributes('email', ['CompletelyFakeTag'], 'test@example.com');

        $this->assertInstanceOf(NullErrors::class, $errors);
        $this->assertFalse($errors->hasErrors());
    }

    public function testAttributeSpecificMethodWithValidClass(): void
    {
        // Test attribute-specific validation with valid class to ensure coverage
        // This will test the branch where class_exists returns true in isSemanticTagClass
        $errors = $this->validator->validateWithAttributes('user_age', ['Teen'], 16);

        $this->assertInstanceOf(NullErrors::class, $errors);
        $this->assertFalse($errors->hasErrors());
    }

    public function testValidateArgsWithNullValue(): void
    {
        // Create a reflection method to test validateArgs with null values
        $testClass = new class {
            public function testMethod(string $email): void
            {
            }
        };

        $reflection = new ReflectionClass($testClass);
        $method = $reflection->getMethod('testMethod');

        // Test that null values are now passed to validation (would previously be skipped with isset)
        // This should throw a TypeError because null is passed to validateEmail(string $email)
        $this->expectException(TypeError::class);
        $this->validator->validateArgs($method, ['email' => null]);
    }
}
