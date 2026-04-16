<?php

declare(strict_types=1);

namespace MyVendor\MyApp\Ldd\RegisterUser;

use function preg_match;

/**
 * Test double for an email format verifier. The real app would inject a
 * domain service here; this one is enough to prove the LDD loop closes.
 */
final class EmailVerifier
{
    public function check(string $email): bool
    {
        return preg_match('/^[^@]+@[^@]+$/', $email) === 1;
    }
}
