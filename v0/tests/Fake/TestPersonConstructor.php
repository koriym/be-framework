<?php

declare(strict_types=1);

namespace Be\Framework\Tests\Fake;

use Ray\InputQuery\Attribute\Input;

/**
 * Test constructor class with name and age parameters for semantic validation
 */
final readonly class TestPersonConstructor
{
    public function __construct(
        #[Input] public string $name,
        #[Input] public int $age
    ) {}
}