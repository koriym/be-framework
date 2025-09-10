<?php

declare(strict_types=1);

namespace Be\Framework\Tests\Fake;

/**
 * Test input object with actual int value
 */
final readonly class ObjectWithIntValue
{
    public function __construct(
        public int $value = 42,
        public ?string $optionalName = null
    ) {}
}