<?php

declare(strict_types=1);

namespace Ray\Framework;

use Ray\InputQuery\Attribute\Input;

/**
 * Class that always fails instantiation (requires impossible parameter)
 */
final class FakeFailingUserB
{
    public function __construct(
        #[Input] public readonly string $name,
        #[Input] public readonly int $anotherRequiredParameter  // This will also fail - not provided
    ) {}
}