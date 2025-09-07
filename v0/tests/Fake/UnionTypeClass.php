<?php

declare(strict_types=1);

namespace Be\Framework\Tests\Fake;

/**
 * Test class with union type parameters
 */
final readonly class UnionTypeClass
{
    public function __construct(
        public int|string $value,
        public ?string $optionalName = null
    ) {}
}