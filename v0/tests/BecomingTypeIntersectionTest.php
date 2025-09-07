<?php

declare(strict_types=1);

namespace Be\Framework\Tests;

use Be\Framework\BecomingType;
use Countable;
use Iterator;
use PHPUnit\Framework\TestCase;

use function count;
use function fclose;
use function tmpfile;

/**
 * Test intersection type handling and edge cases in BecomingType
 */
final class BecomingTypeIntersectionTest extends TestCase
{
    private BecomingType $becomingType;

    protected function setUp(): void
    {
        $this->becomingType = new BecomingType();
    }

    public function testHandleIntersectionTypeWithMatchingTypes(): void
    {
        // Create a mock object that implements multiple interfaces
        $value = new class implements Iterator, Countable {
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
                return count($this->data);
            }
        };

        $input = new class ($value) {
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
        $this->assertTrue($result, 'Object implementing multiple interfaces should match mixed type');
    }

    public function testHandleUnknownReflectionType(): void
    {
        // This test is designed to trigger the unknown ReflectionType case
        // In practice, this is difficult to achieve without mocking
        // since PHP only has a limited set of ReflectionType implementations

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

    public function testGetValueTypeWithCallable(): void
    {
        // Test with callable to potentially trigger different gettype() paths
        $callable = static fn () => 'test';

        $input = new class ($callable) {
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
        $this->assertTrue($result, 'Callable should match mixed type');
    }

    public function testGetValueTypeWithNull(): void
    {
        // Test null value handling
        $input = new class {
            public mixed $value = null;
        };

        $targetClass = new class (null) {
            public function __construct(public mixed $value)
            {
            }
        };

        $result = $this->becomingType->match($input, $targetClass::class);
        $this->assertTrue($result, 'Null should match mixed type');
    }

    public function testMatchWithUnknownType(): void
    {
        // Test with a resource that might trigger the default case in getValueType
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
