<?php

declare(strict_types=1);

namespace Ray\Framework;

use Ray\InputQuery\Attribute\Input;

/**
 * Premium user class (requires isPremium = true)
 */
final class FakePremiumUser
{
    public function __construct(
        #[Input] public readonly string $name,
        #[Input] public readonly string $email,
        #[Input] public readonly bool $isPremium
    ) {
        // This constructor will fail if isPremium is false
        if (!$isPremium) {
            throw new \InvalidArgumentException('Premium user requires isPremium = true');
        }
    }
}