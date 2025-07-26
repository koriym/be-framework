<?php

declare(strict_types=1);

namespace Be\Framework;

use Ray\InputQuery\Attribute\Input;

/**
 * Class that always fails instantiation (requires impossible parameter)
 */
final class FakeFailingUserA
{
    public function __construct(
        #[Input] public readonly string $name,
        #[Input] public readonly string $requiredParameter  // This will fail - not provided
    ) {}
}