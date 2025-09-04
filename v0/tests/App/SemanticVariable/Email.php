<?php

declare(strict_types=1);

namespace Be\App\SemanticVariable;

use Be\Framework\Attribute\Validate;
use DomainException;

use function filter_var;

use const FILTER_VALIDATE_EMAIL;

final class Email
{
    #[Validate]
    public function validateEmail(string $email): void
    {
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new DomainException("Invalid email format: {$email}");
        }
    }

    #[Validate]
    public function validateEmailConfirmation(string $email, string $confirmation): void
    {
        if ($email !== $confirmation) {
            throw new DomainException('Email confirmation does not match');
        }
    }
}
