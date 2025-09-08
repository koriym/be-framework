<?php

declare(strict_types=1);

namespace Be\Framework\Tests\Fake;

/**
 * Test object with name and age properties
 */
final readonly class TestPerson
{
    public function __construct(
        public string $name,
        public int $age
    ) {}
}