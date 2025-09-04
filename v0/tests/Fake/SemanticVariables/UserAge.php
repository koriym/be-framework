<?php

declare(strict_types=1);

namespace Be\Framework\SemanticVariables;

use Be\Framework\Attribute\Validate;
use Be\Framework\SemanticTag\Adult;
use Be\Framework\SemanticTag\Senior;
use DomainException;

final class UserAge
{
    #[Validate]
    public function validateUserAge(int $age): void
    {
        if ($age < 0) {
            throw new DomainException("User age cannot be negative: {$age}");
        }

        if ($age > 120) {
            throw new DomainException("User age cannot exceed 120: {$age}");
        }
    }

    #[Validate]
    public function validateAdult(#[Adult] int $age): void
    {
        // Base validation first
        $this->validateUserAge($age);

        if ($age < 18) {
            throw new DomainException("Adult age must be at least 18: {$age}");
        }
    }

    #[Validate]
    public function validateSenior(#[Senior] int $age): void
    {
        // Base validation first
        $this->validateUserAge($age);

        if ($age < 65) {
            throw new DomainException("Senior age must be at least 65: {$age}");
        }
    }

    #[Validate]
    public function validateTeen(int $age): void
    {
        // Base validation first
        $this->validateUserAge($age);

        if ($age < 13) {
            throw new DomainException("Teen age must be at least 13: {$age}");
        }

        if ($age > 19) {
            throw new DomainException("Teen age must be at most 19: {$age}");
        }
    }
}
