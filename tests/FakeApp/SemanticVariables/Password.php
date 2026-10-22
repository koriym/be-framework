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
            // Deliberately no password value in the message: `errors[].message` is written
            // as-is, with no redaction, so a #[Validate] method for a sensitive variable must
            // never interpolate the rejected value into it.
            throw new DomainException('Password does not meet the minimum length requirement');
        }
    }
}
