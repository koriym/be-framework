<?php

declare(strict_types=1);

namespace Be\Framework\Tests;

use Be\Framework\BecomingType;
use Be\Framework\Tests\Fake\FormalGreeting;
use Be\Framework\Tests\Fake\GreetingInput;
use Be\Framework\Tests\Fake\ObjectWithFloatValue;
use Be\Framework\Tests\Fake\ObjectWithIntValue;
use Be\Framework\Tests\Fake\ObjectWithStringValue;
use Be\Framework\Tests\Fake\UnionTypeClass;
use PHPUnit\Framework\TestCase;

/**
 * Test for BecomingType class using real classes
 *
 * Tests the match() method functionality of checking if an object's properties
 * are compatible with a target class constructor parameters.
 */
final class BecomingTypeTest extends TestCase
{
    private BecomingType $becomingType;

    protected function setUp(): void
    {
        $this->becomingType = new BecomingType();
    }

    public function testCanBeInstantiated(): void
    {
        $becomingType = new BecomingType();
        $this->assertInstanceOf(BecomingType::class, $becomingType);
    }

    public function testMatchWithCompatibleRealClasses(): void
    {
        $input = new GreetingInput('John', 'formal');

        $result = $this->becomingType->match($input, FormalGreeting::class);
        $this->assertTrue($result, 'GreetingInput should match FormalGreeting - compatible properties and types');
    }

    public function testMatchWithUnionTypeAcceptsInt(): void
    {
        $input = new ObjectWithIntValue(42);

        $result = $this->becomingType->match($input, UnionTypeClass::class);
        $this->assertTrue($result, 'Object with int value should match UnionTypeClass expecting int|string');
    }

    public function testMatchWithUnionTypeAcceptsString(): void
    {
        $input = new ObjectWithStringValue('hello');

        $result = $this->becomingType->match($input, UnionTypeClass::class);
        $this->assertTrue($result, 'Object with string value should match UnionTypeClass expecting int|string');
    }

    public function testMatchWithUnionTypeRejectsFloat(): void
    {
        $input = new ObjectWithFloatValue(3.14);

        $result = $this->becomingType->match($input, UnionTypeClass::class);
        $this->assertFalse($result, 'Object with float value should NOT match UnionTypeClass expecting int|string');
    }
}
