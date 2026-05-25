<?php

declare(strict_types=1);

namespace Be\Framework;

use SensitiveParameter;

final class SensitiveCredentialInput
{
    public function __construct(
        public readonly string $username,
        #[SensitiveParameter]
        public readonly string $password,
    ) {
    }
}
