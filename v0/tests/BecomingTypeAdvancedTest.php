<?php

declare(strict_types=1);

namespace Be\Framework\Tests;

use ArrayAccess;
use Be\Framework\BecomingType;
use Countable;
use Exception;
use PHPUnit\Framework\TestCase;
use stdClass;
use Throwable;

use function fclose;
use function fopen;
use function tmpfile;

/**
 * Advanced tests for BecomingType class focusing on the corrected type system
 */
final class BecomingTypeAdvancedTest extends TestCase
{
    private BecomingType $becomingType;

    protected function setUp(): void
    {
        $this->becomingType = new BecomingType();
    }

    public function testMatchWithUnionTypeProperty(): void
    {
        // Create an input object with actual int value (not union type declaration)
        $input = new class {
            public int $value = 42; // Actual value is int
        };

        // Target expects int|string but should match because actual value is int
        $targetClass = new class (42) {
            public function __construct(public int|string $value)
            {
            }
        };

        $result = $this->becomingType->match($input, $targetClass::class);
        $this->assertTrue($result, 'Should match: actual int value is compatible with int|string parameter');
    }

    public function testMatchWithUnionTypePropertyString(): void
    {
        // Create an input object with actual string value
        $input = new class {
            public string $value = 'hello'; // Actual value is string
        };

        // Target expects int|string
        $targetClass = new class ('') {
            public function __construct(public int|string $value)
            {
            }
        };

        $result = $this->becomingType->match($input, $targetClass::class);
        $this->assertTrue($result, 'Should match: actual string value is compatible with int|string parameter');
    }

    public function testMatchWithUnionTypePropertyMismatch(): void
    {
        // Create an input object with float value
        $input = new class {
            public float $value = 3.14; // Actual value is float
        };

        // Target expects int|string (not float)
        $targetClass = new class ('') {
            public function __construct(public int|string $value)
            {
            }
        };

        $result = $this->becomingType->match($input, $targetClass::class);
        $this->assertFalse($result, 'Should not match: actual float value is not compatible with int|string parameter');
    }

    public function testMatchWithNullableType(): void
    {
        $input = new class {
            public string|null $name = null; // Actual value is null
        };

        $targetClass = new class (null) {
            public function __construct(public string|null $name)
            {
            }
        };

        $result = $this->becomingType->match($input, $targetClass::class);
        $this->assertTrue($result, 'Should match: null value is compatible with nullable string parameter');
    }

    public function testMatchWithNullValueToNonNullableType(): void
    {
        $input = new class {
            public string|null $name = null; // Actual value is null
        };

        $targetClass = new class ('') {
            public function __construct(public string $name)
            {
            } // Not nullable
        };

        $result = $this->becomingType->match($input, $targetClass::class);
        $this->assertFalse($result, 'Should not match: null value is not compatible with non-nullable string parameter');
    }

    public function testMatchWithObjectTypes(): void
    {
        $stdClassInstance = new stdClass();

        $input = new class ($stdClassInstance) {
            public function __construct(public stdClass $obj)
            {
            }
        };

        $targetClass = new class (new stdClass()) {
            public function __construct(public stdClass $obj)
            {
            }
        };

        $result = $this->becomingType->match($input, $targetClass::class);
        $this->assertTrue($result, 'Should match: stdClass instance is compatible with stdClass parameter');
    }

    public function testMatchWithObjectTypeMismatch(): void
    {
        $input = new class {
            public stdClass $obj;

            public function __construct()
            {
                $this->obj = new stdClass();
            }
        };

        $targetClass = new class (new Exception()) {
            public function __construct(public Throwable $obj)
            {
            } // Different class
        };

        $result = $this->becomingType->match($input, $targetClass::class);
        $this->assertFalse($result, 'Should not match: stdClass instance is not compatible with Exception parameter');
    }

    public function testRealWorldScenario(): void
    {
        // Similar to the original problem: object with union property but actual specific value
        $input = new class {
            public string $name = 'John';      // Actual value is string
            public string $style = 'casual';   // Actual value is string
        };

        // Target class expects specific types that match the actual values
        $targetClass = new class ('', '') {
            public function __construct(
                public string $name,
                public string $style,
            ) {
            }
        };

        $result = $this->becomingType->match($input, $targetClass::class);
        $this->assertTrue($result, 'Real-world scenario should work: actual values match expected types');
    }

    public function testMatchWithMixedType(): void
    {
        $input = new class {
            public string $value = 'anything';
        };

        $targetClass = new class ('') {
            public function __construct(public mixed $value)
            {
            }
        };

        $result = $this->becomingType->match($input, $targetClass::class);
        $this->assertTrue($result, 'Any type should match mixed parameter');
    }

    public function testMatchWithBooleanType(): void
    {
        $input = new class {
            public bool $flag = true;
        };

        $targetClass = new class (true) {
            public function __construct(public bool $flag)
            {
            }
        };

        $result = $this->becomingType->match($input, $targetClass::class);
        $this->assertTrue($result, 'Boolean value should match boolean parameter');
    }

    public function testMatchWithFloatType(): void
    {
        $input = new class {
            public float $number = 3.14;
        };

        $targetClass = new class (0.0) {
            public function __construct(public float $number)
            {
            }
        };

        $result = $this->becomingType->match($input, $targetClass::class);
        $this->assertTrue($result, 'Float value should match float parameter');
    }

    public function testMatchWithNoConstructor(): void
    {
        $input = new class {
            public string $data = 'test';
        };

        $targetClass = new class {
            // No constructor
        };

        $result = $this->becomingType->match($input, $targetClass::class);
        $this->assertTrue($result, 'Any input should match class with no constructor');
    }

    public function testMatchWithMissingProperty(): void
    {
        $input = new class {
            public string $name = 'John';
            // Missing 'age' property
        };

        $targetClass = new class ('', 0) {
            public function __construct(
                public string $name,
                public int $age, // This property is missing from input
            ) {
            }
        };

        $result = $this->becomingType->match($input, $targetClass::class);
        $this->assertFalse($result, 'Should not match when required property is missing');
    }

    public function testMatchWithIntersectionType(): void
    {
        // Test intersection type handling - this will hit line 70
        // Since PHP doesn't support intersection types in property declarations directly,
        // we need to create a more complex scenario
        $input = new class {
            public object $value;

            public function __construct()
            {
                $this->value = new class implements ArrayAccess, Countable {
                    public function offsetExists($offset): bool
                    {
                        return false;
                    }

                    public function offsetGet($offset): mixed
                    {
                        return null;
                    }

                    public function offsetSet($offset, $value): void
                    {
                    }

                    public function offsetUnset($offset): void
                    {
                    }

                    public function count(): int
                    {
                        return 0;
                    }
                };
            }
        };

        // This will exercise the intersection type path indirectly
        $targetClass = new class ($input->value) {
            public function __construct(public ArrayAccess&Countable $value)
            {
            }
        };

        $result = $this->becomingType->match($input, $targetClass::class);
        $this->assertTrue($result, 'Object implementing ArrayAccess&Countable should match intersection type parameter');
    }

    public function testMatchWithIntersectionTypeFailure(): void
    {
        // Test intersection type handling - failure case where object doesn't implement all required interfaces
        $input = new class {
            public object $value;

            public function __construct()
            {
                // Create an object that only implements ArrayAccess but not Countable
                $this->value = new class implements ArrayAccess {
                    public function offsetExists($offset): bool
                    {
                        return false;
                    }

                    public function offsetGet($offset): mixed
                    {
                        return null;
                    }

                    public function offsetSet($offset, $value): void
                    {
                    }

                    public function offsetUnset($offset): void
                    {
                    }
                };
            }
        };

        // Target class requires both ArrayAccess AND Countable
        // Use a dummy object that implements both interfaces for constructor
        $dummy = new class implements ArrayAccess, Countable {
            public function offsetExists($offset): bool
            {
                return false;
            }

            public function offsetGet($offset): mixed
            {
                return null;
            }

            public function offsetSet($offset, $value): void
            {
            }

            public function offsetUnset($offset): void
            {
            }

            public function count(): int
            {
                return 0;
            }
        };

        $targetClass = new class ($dummy) {
            public function __construct(public ArrayAccess&Countable $value)
            {
            }
        };

        $result = $this->becomingType->match($input, $targetClass::class);
        $this->assertFalse($result, 'Object implementing only ArrayAccess should not match ArrayAccess&Countable intersection type');
    }

    public function testGetValueTypeWithResource(): void
    {
        // Test the default case in getValueType (line 150)
        // We need to access this indirectly through type matching
        $resource = fopen('php://memory', 'r');
        $input = new class ($resource) {
            public function __construct(public mixed $value)
            {
            }
        };

        $targetClass = new class (null) {
            public function __construct(public mixed $value)
            {
            }
        };

        $result = $this->becomingType->match($input, $targetClass::class);
        $this->assertTrue($result, 'Resource should match mixed type');

        fclose($resource);
    }

    public function testMatchWithArrayType(): void
    {
        // Test array type handling to cover more getValueType cases
        $input = new class {
            public array $data = ['test'];
        };

        $targetClass = new class ([]) {
            public function __construct(public array $data)
            {
            }
        };

        $result = $this->becomingType->match($input, $targetClass::class);
        $this->assertTrue($result, 'Array should match array type');
    }

    public function testMatchWithStringType(): void
    {
        // Test string type handling
        $input = new class {
            public string $text = 'hello';
        };

        $targetClass = new class ('') {
            public function __construct(public string $text)
            {
            }
        };

        $result = $this->becomingType->match($input, $targetClass::class);
        $this->assertTrue($result, 'String should match string type');
    }

    public function testMatchWithResourceType(): void
    {
        // Test resource type to trigger default case in getValueType
        $resource = tmpfile();
        $input = new class ($resource) {
            public function __construct(public mixed $resource)
            {
            }
        };

        $targetClass = new class (null) {
            public function __construct(public mixed $resource)
            {
            }
        };

        $result = $this->becomingType->match($input, $targetClass::class);
        $this->assertTrue($result, 'Resource should match mixed type');

        fclose($resource);
    }
}
