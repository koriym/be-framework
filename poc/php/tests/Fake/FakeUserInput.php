<?php

declare(strict_types=1);

namespace Be\Framework;

use Be\Framework\Be;
use Ray\InputQuery\Attribute\Input;

/**
 * Initial user input class for testing metamorphosis
 */
final class FakeUserInput
{
    public function __construct(
        #[Input] public readonly string $name,
        #[Input] public readonly string $email,
        #[Input] public readonly int $age
    ) {}
}