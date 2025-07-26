<?php

declare(strict_types=1);

namespace Be\Framework;

use Be\Framework\Be;
use Ray\InputQuery\Attribute\Input;

/**
 * Validated user class that becomes RegisteredUser
 */
#[Be(FakeRegisteredUser::class)]
final class FakeValidatedUser
{
    public function __construct(
        #[Input] public readonly string $name,
        #[Input] public readonly string $email,
        #[Input] public readonly int $age
    ) {
        // Validation logic could be here
        if (empty($name)) {
            throw new \InvalidArgumentException('Name cannot be empty');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email format');
        }
        if ($age < 0) {
            throw new \InvalidArgumentException('Age must be positive');
        }
    }
}