<?php

declare(strict_types=1);

namespace Ray\Framework;

use Ray\InputQuery\Attribute\Input;

/**
 * Regular user class (fallback for non-premium users)
 */
final class FakeRegularUser
{
    public function __construct(
        #[Input] public readonly string $name,
        #[Input] public readonly string $email,
        #[Input] public readonly bool $isPremium = false
    ) {
        // This constructor accepts any isPremium value
    }
}