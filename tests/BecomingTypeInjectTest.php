<?php

declare(strict_types=1);

namespace Be\Framework\Tests;

use Be\Framework\BecomingType;
use PHPUnit\Framework\TestCase;
use Ray\Di\Di\Inject;

final class BecomingTypeInjectTest extends TestCase
{
    private BecomingType $becomingType;

    protected function setUp(): void
    {
        $this->becomingType = new BecomingType();
    }

    public function testMatchSkipsInjectParameters(): void
    {
        $source = new class {
            public string $name = 'John';
            // Note: no 'service' property - this should not be required for matching
        };

        $result = $this->becomingType->match($source, ClassWithInjectParameter::class);

        $this->assertTrue($result, 'Should match even when #[Inject] parameter property is missing');
    }

    public function testMatchWithMixedInjectAndNormalParameters(): void
    {
        $source = new class {
            public string $name = 'John';
            public int $age = 25;
            // Note: no 'logger' property - this should not be required for matching
        };

        $result = $this->becomingType->match($source, ClassWithMixedParameters::class);

        $this->assertTrue($result, 'Should match when all non-inject parameters are present');
    }

    public function testMatchFailsWhenNormalParameterMissing(): void
    {
        $source = new class {
            public string $name = 'John';
            // Note: missing 'age' property - this should cause match to fail
        };

        $result = $this->becomingType->match($source, ClassWithMixedParameters::class);

        $this->assertFalse($result, 'Should fail when non-inject parameter property is missing');
    }

    public function testMatchWithMultipleInjectParameters(): void
    {
        $source = new class {
            public string $value = 'test';
            // Note: no 'serviceA' or 'serviceB' properties
        };

        $result = $this->becomingType->match($source, ClassWithMultipleInjectParameters::class);

        $this->assertTrue($result, 'Should match when only non-inject parameters are present');
    }

    public function testMatchWithOnlyInjectParameters(): void
    {
        $source = new class {
            // Empty object - no properties
        };

        $result = $this->becomingType->match($source, ClassWithOnlyInjectParameters::class);

        $this->assertTrue($result, 'Should match empty object when all parameters are #[Inject]');
    }

    public function testGetMismatchReasonsForMissingProperty(): void
    {
        $source = new class {
            public string $name = 'John';
            // Missing 'age' property
        };

        $reasons = $this->becomingType->getMismatchReasons($source, ClassWithMixedParameters::class);

        $this->assertArrayHasKey('age', $reasons);
        $this->assertEquals('Property missing from source object', $reasons['age']);
        $this->assertArrayNotHasKey('logger', $reasons, '#[Inject] parameters should be skipped');
    }

    public function testGetMismatchReasonsForTypeMismatch(): void
    {
        $source = new class {
            public string $name = 'John';
            public string $age = 'twenty-five'; // Type mismatch: should be int
        };

        $reasons = $this->becomingType->getMismatchReasons($source, ClassWithMixedParameters::class);

        $this->assertArrayHasKey('age', $reasons);
        $this->assertStringContainsString('Type mismatch: expected int, got string', $reasons['age']);
        $this->assertArrayNotHasKey('logger', $reasons, '#[Inject] parameters should be skipped');
    }

    public function testGetMismatchReasonsWithNoMismatches(): void
    {
        $source = new class {
            public string $name = 'John';
            public int $age = 25;
            // No 'logger' property needed (it's injected)
        };

        $reasons = $this->becomingType->getMismatchReasons($source, ClassWithMixedParameters::class);

        $this->assertEmpty($reasons, 'Should have no mismatch reasons when all required properties match');
    }
}

// Test fixture classes
final class ClassWithInjectParameter
{
    public function __construct(
        public readonly string $name,
        #[Inject]
        public readonly object $service,
    ) {
    }
}

final class ClassWithMixedParameters
{
    public function __construct(
        public readonly string $name,
        public readonly int $age,
        #[Inject]
        public readonly object $logger,
    ) {
    }
}

final class ClassWithMultipleInjectParameters
{
    public function __construct(
        public readonly string $value,
        #[Inject]
        public readonly object $serviceA,
        #[Inject]
        public readonly object $serviceB,
    ) {
    }
}

final class ClassWithOnlyInjectParameters
{
    public function __construct(
        #[Inject]
        public readonly object $serviceA,
        #[Inject]
        public readonly object $serviceB,
    ) {
    }
}
