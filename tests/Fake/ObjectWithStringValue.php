<?php

declare(strict_types=1);

namespace Be\Framework\Tests\Fake;

/**
 * Test input object with actual string value
 */
final readonly class ObjectWithStringValue
{
    public function __construct(
        public string $value = 'hello',
        public ?string $optionalName = null
    ) {}
}