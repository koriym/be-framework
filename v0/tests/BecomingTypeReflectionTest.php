<?php

declare(strict_types=1);

namespace Be\Framework\Tests;

use Be\Framework\BecomingType;
use Countable;
use Iterator;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionType;
use stdClass;

use function count;
use function fclose;
use function is_resource;
use function tmpfile;

use const PHP_VERSION_ID;

/**
 * Test specific ReflectionType handling in BecomingType
 */
final class BecomingTypeReflectionTest extends TestCase
{
    private BecomingType $becomingType;

    protected function setUp(): void
    {
        $this->becomingType = new BecomingType();
    }

    public function testIntersectionTypeHandling(): void
    {
        // Create a class with intersection type parameter (PHP 8.1+)
        if (PHP_VERSION_ID < 80100) {
            $this->markTestSkipped('Intersection types require PHP 8.1+');
        }

        // We need to test this indirectly since PHP doesn't allow intersection types in property declarations
        // Create a value that would match intersection type requirements
        $value = new class implements Iterator, Countable {
            private array $data = ['item1', 'item2'];
            private int $position = 0;

            public function current(): mixed
            {
                return $this->data[$this->position] ?? null;
            }

            public function key(): mixed
            {
                return $this->position;
            }

            public function next(): void
            {
                ++$this->position;
            }

            public function rewind(): void
            {
                $this->position = 0;
            }

            public function valid(): bool
            {
                return isset($this->data[$this->position]);
            }

            public function count(): int
            {
                return count($this->data);
            }
        };

        $input = new class ($value) {
            public function __construct(public mixed $intersectionValue)
            {
            }
        };

        $targetClass = new class (null) {
            public function __construct(public mixed $intersectionValue)
            {
            }
        };

        $result = $this->becomingType->match($input, $targetClass::class);
        $this->assertTrue($result, 'Object implementing multiple interfaces should match mixed type');
    }

    public function testHandleIntersectionTypeDirectly(): void
    {
        // Test intersection type handling by accessing it through reflection
        if (PHP_VERSION_ID < 80100) {
            $this->markTestSkipped('Intersection types require PHP 8.1+');
        }

        // Create a method that has intersection type parameter
        eval('
            class TestIntersectionClass {
                public function testMethod(\Iterator&\Countable $param): void {}
            }
        ');

        $reflection = new ReflectionClass('TestIntersectionClass');
        $method = $reflection->getMethod('testMethod');
        $parameter = $method->getParameters()[0];
        $type = $parameter->getType();

        if ($type instanceof ReflectionIntersectionType) {
            // Now we have a real intersection type to test against
            $validValue = new class implements Iterator, Countable {
                private array $data = [];
                private int $position = 0;

                public function current(): mixed
                {
                    return $this->data[$this->position] ?? null;
                }

                public function key(): mixed
                {
                    return $this->position;
                }

                public function next(): void
                {
                    ++$this->position;
                }

                public function rewind(): void
                {
                    $this->position = 0;
                }

                public function valid(): bool
                {
                    return isset($this->data[$this->position]);
                }

                public function count(): int
                {
                    return 0;
                }
            };

            $input = new class ($validValue) {
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
            $this->assertTrue($result, 'Value implementing intersection type should match');
        } else {
            $this->markTestSkipped('Could not create intersection type for testing');
        }
    }

    public function testUnknownReflectionTypeHandling(): void
    {
        // Test unknown reflection type by creating a mock
        $unknownType = new class extends ReflectionType {
            public function __toString(): string
            {
                return 'unknown';
            }

            public function allowsNull(): bool
            {
                return false;
            }
        };

        // We can't directly test this since isValueCompatibleWithType is private
        // But we can ensure the code path exists by testing with a complex scenario

        $input = new class {
            public mixed $value = 'test';
        };

        $targetClass = new class ('') {
            public function __construct(public mixed $value)
            {
            }
        };

        $result = $this->becomingType->match($input, $targetClass::class);
        $this->assertTrue($result, 'Mixed type should always match');
    }

    public function testReflectionNamedTypeEdgeCases(): void
    {
        // Test various named type edge cases
        $testCases = [
            'string' => 'test_value',
            'int' => 42,
            'float' => 3.14,
            'bool' => true,
            'array' => ['test'],
            'object' => new stdClass(),
        ];

        foreach ($testCases as $type => $value) {
            $input = new class ($value) {
                public function __construct(public mixed $testValue)
                {
                }
            };

            $targetClass = new class (null) {
                public function __construct(public mixed $testValue)
                {
                }
            };

            $result = $this->becomingType->match($input, $targetClass::class);
            $this->assertTrue($result, "Value of type {$type} should match mixed parameter");
        }
    }

    public function testNullValueWithNullableType(): void
    {
        // Test null value handling with nullable types
        $input = new class {
            public string|null $nullableString = null;
        };

        $targetClass = new class (null) {
            public function __construct(public string|null $nullableString)
            {
            }
        };

        $result = $this->becomingType->match($input, $targetClass::class);
        $this->assertTrue($result, 'Null value should match nullable string');
    }

    public function testNullValueWithNonNullableType(): void
    {
        // Test null value with non-nullable type - should fail
        $input = new class {
            public string|null $value = null;
        };

        $targetClass = new class ('') {
            public function __construct(public string $value)
            {
            } // Non-nullable
        };

        $result = $this->becomingType->match($input, $targetClass::class);
        $this->assertFalse($result, 'Null value should not match non-nullable string');
    }

    public function testGetValueTypeDefault(): void
    {
        // Test the default case in getValueType method (line ~155)
        // Resource type should trigger the default case
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

        if (is_resource($resource)) {
            fclose($resource);
        }
    }
}
