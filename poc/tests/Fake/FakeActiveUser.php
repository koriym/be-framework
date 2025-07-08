<?php

declare(strict_types=1);

namespace Ray\Framework;

use Ray\InputQuery\Attribute\Input;

/**
 * Final active user class - no further metamorphosis
 */
final class FakeActiveUser
{
    public function __construct(
        #[Input] public readonly string $name,
        #[Input] public readonly string $email,
        #[Input] public readonly int $age,
        #[Input] public readonly string $id,
        #[Input] public ?\DateTimeImmutable $activatedAt = null
    ) {
        // Activation logic - set activation time if not provided
        if ($activatedAt === null) {
            $this->activatedAt = new \DateTimeImmutable();
        }
    }
}
