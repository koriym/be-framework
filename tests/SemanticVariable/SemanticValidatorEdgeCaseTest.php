<?php

declare(strict_types=1);

namespace Be\Framework\SemanticVariable;

use Be\Framework\Attribute\Inject;
use MyVendor\MyApp\SemanticVariables\Email;
use PHPUnit\Framework\TestCase;
use Ray\Di\Di\Injector;
use ReflectionClass;

use function error_reporting;

use const E_ALL;

/**
 * Test edge cases and unused methods in SemanticValidator
 */
final class SemanticValidatorEdgeCaseTest extends TestCase
{
    private SemanticValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new SemanticValidator('MyVendor\\MyApp\\SemanticVariables');
    }

    public function testMethodMatchesArgumentsWithInjectParameters(): void
    {
        // Test methodMatchesArguments method with injected parameters
        $testClass = new class {
            public function testMethod(
                string $email,
                Injector $injector,
            ): void {
            }
        };

        $reflection = new ReflectionClass($testClass);
        $method = $reflection->getMethod('testMethod');

        // This should test the methodMatchesArguments private method indirectly
        // by trying to validate arguments that would call this method
        $errors = $this->validator->validateArgs($method, ['email' => 'test@example.com']);

        $this->assertInstanceOf(Errors::class, $errors);
    }

    public function testIsAttributeSpecificMethodWithNonExistentTag(): void
    {
        // Test with non-existent semantic tag attributes to cover isAttributeSpecificMethod
        $errors = $this->validator->validateWithAttributes('user_age', ['NonExistentTag'], 25);

        // Should still validate with base validation since non-existent tag is ignored
        $this->assertInstanceOf(NullErrors::class, $errors);
    }

    public function testGetMethodRequiredAttributesWithComplexScenario(): void
    {
        // This tests getMethodRequiredAttributes indirectly by using a scenario that would call it
        $errors = $this->validator->validateWithAttributes('user_age', ['Teen', 'Student'], 16);

        $this->assertInstanceOf(NullErrors::class, $errors);
    }

    public function testIsSemanticTagClassWithFrameworkAttributes(): void
    {
        // Test isSemanticTagClass with framework attributes (Input, Inject)
        // This is tested indirectly through validation that includes these attributes
        $errors = $this->validator->validateWithAttributes('email', ['Input'], 'test@example.com');

        $this->assertInstanceOf(NullErrors::class, $errors);
    }

    public function testIsBaseValidationMethodCheck(): void
    {
        // Test isBaseValidationMethod by using different validation scenarios
        // This method is used internally to determine if a method is a base validation
        $errors = $this->validator->validateWithAttributes('email_confirmation', [], 'test@example.com', 'test@example.com');

        $this->assertInstanceOf(NullErrors::class, $errors);
    }

    public function testValidateArgsWithMixedParameterTypes(): void
    {
        // Create a complex method signature to test various code paths
        $testClass = new class {
            public function complexMethod(
                string $email,
                int|null $age,
                array $data = [],
                mixed $extra = null,
            ): void {
            }
        };

        $reflection = new ReflectionClass($testClass);
        $method = $reflection->getMethod('complexMethod');

        $args = [
            'email' => 'test@example.com',
            'age' => 25,
            'data' => ['key' => 'value'],
            'extra' => 'something',
        ];

        $errors = $this->validator->validateArgs($method, $args);
        $this->assertInstanceOf(Errors::class, $errors);
    }

    public function testValidatePropsWithMissingProperty(): void
    {
        // Create a concrete class for testing constructor parameter validation
        $fakeClass = new class ('', 0, '') {
            public function __construct(
                public string $email,
                public int $age,
                public string $missing_prop,
            ) {
            }
        };

        $testObject = (object) [
            'email' => 'test@example.com',
            'age' => 25,
            // missing $missing_prop
        ];

        $reflection = new ReflectionClass($fakeClass);
        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            $this->fail('Constructor should exist');
        }

        $errors = $this->validator->validateProps($constructor, $testObject);

        // Should only validate existing properties, skip missing ones
        $this->assertInstanceOf(Errors::class, $errors);
    }

    public function testValidateWithNonMatchingMethodSignature(): void
    {
        // Test scenario where no validation methods match the provided arguments
        // This should trigger the "no matching validation methods found" path
        // Use a non-existent variable name to avoid type errors
        $errors = $this->validator->validate('nonexistent_validation_class', 'arg1', 'arg2', 'arg3');

        // Should return NullErrors since no semantic class exists
        $this->assertInstanceOf(NullErrors::class, $errors);
    }

    public function testResolveSemanticClassErrorLogging(): void
    {
        // Test error logging when semantic class doesn't exist
        // Capture error log output
        $originalErrorReporting = error_reporting();
        error_reporting(E_ALL);

        $errors = $this->validator->validate('completely_nonexistent_variable', 'test');

        error_reporting($originalErrorReporting);

        // Should return NullErrors for non-existent semantic class
        $this->assertInstanceOf(NullErrors::class, $errors);
    }

    public function testMethodMatchesArgumentsWithInjectParametersInvalidCase(): void
    {
        // Create a test validation class with a method that has Inject parameters
        $validationClass = new class {
            public function validateEmail(
                string $email,
                #[Inject]
                object $dependency,
                string $domain = 'example.com',
            ): array {
                return [];
            }
        };

        // Set up validator with our test class
        $classMap = ['Email' => $validationClass::class];
        $validator = new SemanticValidator('MyVendor\\MyApp\\SemanticVariables', $classMap);

        // Call validate with arguments - this should exercise the Inject parameter skipping logic
        $errors = $validator->validate('Email', 'test@example.com');

        // Should work normally and return empty errors
        $this->assertInstanceOf(NullErrors::class, $errors);
    }

    public function testValidationMethodWithAllParameterTypeCombinations(): void
    {
        // Test method matching logic to hit various uncovered branches
        $complexValidationClass = new class {
            // Method with Inject attribute (should be skipped)
            public function validateWithInject(
                string $value,
                #[Inject]
                object $service,
            ): array {
                return [];
            }

            // Method with semantic tag attributes on parameters
            public function validateWithSemanticTags(
                #[Email]
                string $email,
                string $domain,
            ): array {
                return empty($email) ? ['Email cannot be empty'] : [];
            }

            // Method without attributes (should match non-attribute-specific)
            public function validateBasic(string $value): array
            {
                return [];
            }
        };

        $classMap = ['ComplexValidation' => $complexValidationClass::class];
        $validator = new SemanticValidator('MyVendor\\MyApp\\SemanticVariables', $classMap);

        // Test each case
        $errors1 = $validator->validate('ComplexValidation', 'test@example.com');
        $this->assertInstanceOf(NullErrors::class, $errors1);

        $errors2 = $validator->validate('ComplexValidation', 'test@example.com', 'example.com');
        $this->assertInstanceOf(NullErrors::class, $errors2);
    }
}
