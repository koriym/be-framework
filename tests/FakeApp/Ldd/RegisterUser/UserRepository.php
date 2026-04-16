<?php

declare(strict_types=1);

namespace MyVendor\MyApp\Ldd\RegisterUser;

/**
 * Test double for a user repository that returns a deterministic id on
 * insert. The LDD reference spec (`been.json`) fixes the expected id to 42,
 * and this repo honours that — keeps the test data stable across runs.
 */
final class UserRepository
{
    /** @param array{email: string} $row */
    public function insert(array $row): int
    {
        return 42;
    }
}
