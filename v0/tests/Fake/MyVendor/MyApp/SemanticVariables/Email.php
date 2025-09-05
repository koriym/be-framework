<?php

declare(strict_types=1);

namespace Be\Framework\Fake\MyVendor\MyApp\SemanticVariables;

use Be\Framework\Attribute\Validate;
use DomainException;

final class Email
{
    #[Validate]
    public function validateEmail(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new DomainException("Invalid email format: {$email}");
        }
    }
    
    #[Validate]
    public function validateConfirmation(string $email, string $confirmation): void
    {
        if ($email !== $confirmation) {
            throw new DomainException("Email confirmation does not match");
        }
    }
}