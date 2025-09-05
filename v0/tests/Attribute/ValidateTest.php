<?php

declare(strict_types=1);

namespace Be\Framework\Attribute;

use Attribute;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class ValidateTest extends TestCase
{
    public function testValidateAttributeExists(): void
    {
        $reflection = new ReflectionClass(Validate::class);

        $this->assertTrue($reflection->isFinal());
        // Check if it has Attribute attribute
        $attributes = $reflection->getAttributes(Attribute::class);
        $this->assertNotEmpty($attributes);
    }

    public function testValidateAttributeCanBeInstantiated(): void
    {
        $validate = new Validate();

        $this->assertInstanceOf(Validate::class, $validate);
    }
}
