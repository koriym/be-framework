<?php

declare(strict_types=1);

namespace Be\Framework\Tests\Fake;

/**
 * Test target class expecting formal greeting parameters
 */
final readonly class FormalGreeting
{
    public function __construct(
        public string $name,
        public string $style
    ) {}
}