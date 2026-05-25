<?php

declare(strict_types=1);

namespace Be\Framework;

use SensitiveParameter;

/**
 * Locks in the order: uninitialized properties are reported as null
 * BEFORE the sensitivity check runs, so a never-assigned sensitive
 * property does not get the misleading `[REDACTED]` placeholder.
 */
final class UninitializedSensitiveInput
{
    public string $password;

    public function __construct(
        public readonly string $username,
        #[SensitiveParameter]
        string $password = '',
    ) {
        if ($password !== '') {
            $this->password = $password;
        }
    }
}
