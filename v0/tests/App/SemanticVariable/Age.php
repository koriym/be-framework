<?php

declare(strict_types=1);

namespace Be\App\SemanticVariable;

use Be\Framework\Attribute\Validate;
use DomainException;

final class Age
{
    #[Validate]
    public function validateRange(int $age): void
    {
        if ($age < 0) {
            throw new DomainException("Age cannot be negative: {$age}");
        }

        if ($age > 150) {
            throw new DomainException("Age cannot exceed 150: {$age}");
        }
    }
}
