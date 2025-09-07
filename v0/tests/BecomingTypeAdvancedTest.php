<?php

declare(strict_types=1);

namespace Be\Framework\Tests;

use Be\Framework\BecomingType;
use Exception;
use PHPUnit\Framework\TestCase;
use stdClass;
use Throwable;

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
}
