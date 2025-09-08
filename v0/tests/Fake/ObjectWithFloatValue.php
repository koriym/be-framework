<?php

declare(strict_types=1);

namespace Be\Framework\Tests\Fake;

/**
 * Test input object with actual float value (incompatible with int|string)
 */
final readonly class ObjectWithFloatValue
{
    public function __construct(
        public float $value = 3.14,
        public ?string $optionalName = null
    ) {}
}