<?php

declare(strict_types=1);

namespace Be\Framework\Tests\Fake;

/**
 * Test input class for BecomingType testing
 */
final readonly class GreetingInput
{
    public function __construct(
        public string $name,
        public string $style
    ) {}
}