<?php

declare(strict_types=1);

namespace Be\Framework;

use Be\Framework\Be;
use Ray\InputQuery\Attribute\Input;

/**
 * Registered user class that becomes ActiveUser
 */
#[Be(FakeActiveUser::class)]
final class FakeRegisteredUser
{
    public function __construct(
        #[Input] public readonly string $name,
        #[Input] public readonly string $email,
        #[Input] public readonly int $age,
        #[Input] public string|null $id = null
    ) {
        // Registration logic - assign ID if not provided
        if ($id === null) {
            $this->id = uniqid('user_');
        }
    }
}
