<?php

declare(strict_types=1);

namespace Be\App\SemanticVariable;

use Be\Framework\Attribute\Validate;
use DomainException;

final class Age
{
    #[Validate]
    public function validateAge(int $age): void
    {
        if ($age < 0) {
            throw new DomainException('Age cannot be negative');
        }

        if ($age > 120) {
            throw new DomainException('Age cannot be greater than 120 years');
        }
    }

    #[Validate]
    public function validateTeen(int $age): void
    {
        $this->validateAge($age);

        if ($age < 13) {
            throw new DomainException('Teen age must be at least 13 years');
        }

        if ($age > 19) {
            throw new DomainException('Teen age must be at most 19 years');
        }
    }
}
