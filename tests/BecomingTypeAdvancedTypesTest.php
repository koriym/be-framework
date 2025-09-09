<?php

declare(strict_types=1);

namespace Be\Framework\Tests;

use ArrayAccess;
use Be\Framework\BecomingType;
use Be\Framework\Tests\Fake\ArrayAccessCountable;
use Be\Framework\Tests\Fake\ArrayAccessOnly;
use Countable;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;
use stdClass;

final class BecomingTypeAdvancedTypesTest extends TestCase
{
    private BecomingType $becomingType;

    protected function setUp(): void
    {
        $this->becomingType = new BecomingType();
    }

    public function testUnionTypeWithIntMatches(): void
    {
        $source = new class {
            public int $value = 42;
        };

        $result = $this->becomingType->match($source, ClassWithUnionType::class);

        $this->assertTrue($result, 'Should match when int is provided for int|string union type');
    }

    public function testUnionTypeWithStringMatches(): void
    {
        $source = new class {
            public string $value = 'hello';
        };

        $result = $this->becomingType->match($source, ClassWithUnionType::class);

        $this->assertTrue($result, 'Should match when string is provided for int|string union type');
    }

    public function testUnionTypeWithInvalidTypeFailsMatch(): void
    {
        $source = new class {
            public float $value = 3.14;
        };

        $result = $this->becomingType->match($source, ClassWithUnionType::class);

        $this->assertFalse($result, 'Should fail when float is provided for int|string union type');
    }

    public function testUnionTypeWithNullableSuccess(): void
    {
        $source = new class {
            public int|null $value = null;
        };

        $result = $this->becomingType->match($source, ClassWithNullableUnionType::class);

        $this->assertTrue($result, 'Should match when null is provided for nullable union type');
    }

    public function testIntersectionTypeSuccess(): void
    {
        $source = new class {
            public ArrayAccessCountable $value;

            public function __construct()
            {
                $this->value = new ArrayAccessCountable();
            }
        };

        $result = $this->becomingType->match($source, ClassWithIntersectionType::class);

        $this->assertTrue($result, 'Should match when object implements both interfaces in intersection');
    }

    public function testIntersectionTypeFailure(): void
    {
        $source = new class {
            public ArrayAccessOnly $value;

            public function __construct()
            {
                $this->value = new ArrayAccessOnly();
            }
        };

        $result = $this->becomingType->match($source, ClassWithIntersectionType::class);

        $this->assertFalse($result, 'Should fail when object implements only one interface in intersection');
    }

    public function testComplexObjectTypeCompatibility(): void
    {
        $source = new class {
            public DateTime $createdAt;

            public function __construct()
            {
                $this->createdAt = new DateTime();
            }
        };

        $result = $this->becomingType->match($source, ClassWithComplexObjectType::class);

        $this->assertTrue($result, 'Should match when DateTime object is provided');
    }

    public function testComplexObjectTypeInheritance(): void
    {
        $source = new class {
            public DateTimeImmutable $createdAt;

            public function __construct()
            {
                $this->createdAt = new DateTimeImmutable();
            }
        };

        $result = $this->becomingType->match($source, ClassWithDateTimeInterface::class);

        $this->assertTrue($result, 'Should match when DateTimeImmutable is provided for DateTimeInterface');
    }

    public function testGetMismatchReasonsForUnionType(): void
    {
        $source = new class {
            public float $value = 3.14;
        };

        $reasons = $this->becomingType->getMismatchReasons($source, ClassWithUnionType::class);

        $this->assertArrayHasKey('value', $reasons);
        $this->assertStringContainsString('Type mismatch: expected string|int, got double', $reasons['value']);
    }

    public function testGetMismatchReasonsForIntersectionType(): void
    {
        $source = new class {
            public ArrayAccessOnly $value;

            public function __construct()
            {
                $this->value = new ArrayAccessOnly();
            }
        };

        $reasons = $this->becomingType->getMismatchReasons($source, ClassWithIntersectionType::class);

        $this->assertArrayHasKey('value', $reasons);
        $this->assertStringContainsString('Type mismatch:', $reasons['value']);
        $this->assertStringContainsString('ArrayAccess&Countable', $reasons['value']);
    }

    public function testMixedTypeAcceptsAnyValue(): void
    {
        $source = new class {
            public string $stringValue = 'test';
            public int $intValue = 42;
            public array $arrayValue = [1, 2, 3];
            public object $objectValue;

            public function __construct()
            {
                $this->objectValue = new stdClass();
            }
        };

        $result = $this->becomingType->match($source, ClassWithMixedTypes::class);

        $this->assertTrue($result, 'Mixed type should accept any value type');
    }

    public function testGenericObjectType(): void
    {
        $source = new class {
            public object $service;

            public function __construct()
            {
                $this->service = new stdClass();
            }
        };

        $result = $this->becomingType->match($source, ClassWithGenericObjectType::class);

        $this->assertTrue($result, 'Generic object type should accept any object');
    }

    public function testGetMismatchReasonsWithNullValue(): void
    {
        $source = new class {
            public string|null $value = null;
        };

        $reasons = $this->becomingType->getMismatchReasons($source, ClassWithNonNullableString::class);

        $this->assertArrayHasKey('value', $reasons);
        $this->assertStringContainsString('Type mismatch: expected string, got null', $reasons['value']);
    }

    public function testGetMismatchReasonsWithCustomObjectType(): void
    {
        $source = new class {
            public stdClass $service;

            public function __construct()
            {
                $this->service = new stdClass();
            }
        };

        $reasons = $this->becomingType->getMismatchReasons($source, ClassWithCustomObjectType::class);

        $this->assertArrayHasKey('service', $reasons);
        $this->assertStringContainsString('Type mismatch: expected DateTime, got stdClass', $reasons['service']);
    }

    public function testGetMismatchReasonsWithBooleanType(): void
    {
        $source = new class {
            public bool $flag = true;
        };

        $reasons = $this->becomingType->getMismatchReasons($source, ClassWithIntType::class);

        $this->assertArrayHasKey('flag', $reasons);
        $this->assertStringContainsString('Type mismatch: expected int, got bool', $reasons['flag']);
    }

    public function testGetMismatchReasonsWithArrayType(): void
    {
        $source = new class {
            public array $data = [1, 2, 3];
        };

        $reasons = $this->becomingType->getMismatchReasons($source, ClassWithStringType::class);

        $this->assertArrayHasKey('data', $reasons);
        $this->assertStringContainsString('Type mismatch: expected string, got array', $reasons['data']);
    }
}

// Test fixture classes for Union types
final class ClassWithUnionType
{
    public function __construct(
        public readonly int|string $value,
    ) {
    }
}

final class ClassWithNullableUnionType
{
    public function __construct(
        public readonly int|string|null $value,
    ) {
    }
}

// Test fixture classes for Intersection types
final class ClassWithIntersectionType
{
    public function __construct(
        public readonly ArrayAccess&Countable $value,
    ) {
    }
}

// Test fixture classes for complex object types
final class ClassWithComplexObjectType
{
    public function __construct(
        public readonly DateTime $createdAt,
    ) {
    }
}

final class ClassWithDateTimeInterface
{
    public function __construct(
        public readonly DateTimeInterface $createdAt,
    ) {
    }
}

final class ClassWithMixedTypes
{
    public function __construct(
        public readonly mixed $stringValue,
        public readonly mixed $intValue,
        public readonly mixed $arrayValue,
        public readonly mixed $objectValue,
    ) {
    }
}

final class ClassWithGenericObjectType
{
    public function __construct(
        public readonly object $service,
    ) {
    }
}

// Additional test fixture classes for edge cases
final class ClassWithNonNullableString
{
    public function __construct(
        public readonly string $value,
    ) {
    }
}

final class ClassWithCustomObjectType
{
    public function __construct(
        public readonly DateTime $service,
    ) {
    }
}

final class ClassWithIntType
{
    public function __construct(
        public readonly int $flag,
    ) {
    }
}

final class ClassWithStringType
{
    public function __construct(
        public readonly string $data,
    ) {
    }
}
