<?php

declare(strict_types=1);

namespace MyVendor\MyApp\SemanticVariables;

use Be\Framework\Attribute\Validate;
use DomainException;
use MyVendor\MyApp\SemanticTag\Adult;
use MyVendor\MyApp\SemanticTag\Senior;
use MyVendor\MyApp\SemanticTag\Teen;

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
    public function validateAdult(#[Adult]
    int $age,): void
    {
        // Base validation first
        $this->validateUserAge($age);

        if ($age < 18) {
            throw new DomainException("Adult age must be at least 18: {$age}");
        }
    }

    #[Validate]
    public function validateSenior(#[Senior]
    int $age,): void
    {
        // Base validation first
        $this->validateUserAge($age);

        if ($age < 65) {
            throw new DomainException("Senior age must be at least 65: {$age}");
        }
    }

    #[Validate]
    public function validateTeen(#[Teen]
    int $age,): void
    {
        // Base validation first
        $this->validateUserAge($age);

        if ($age < 13) {
            throw new TeenAgeTooYoungException($age);
        }

        if ($age > 19) {
            throw new TeenAgeTooOldException($age);
        }
    }
}
