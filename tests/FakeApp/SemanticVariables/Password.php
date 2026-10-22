<?php

declare(strict_types=1);

namespace MyVendor\MyApp\SemanticVariables;

use Be\Framework\Attribute\Validate;
use DomainException;

use function strlen;

final class Password
{
    #[Validate]
    public function validatePassword(string $password): void
    {
        if (strlen($password) < 8) {
            // Deliberately no password value in the message: that is exactly what the
            // log's redacted `errors[].value` exists to recover for a reader.
            throw new DomainException('Password does not meet the minimum length requirement');
        }
    }
}
