<?php

declare(strict_types=1);

namespace MyVendor\MyApp\Ldd\RegisterUser;

use Be\Framework\Attribute\Be;

#[Be(RegisteredUser::class)]
final class UnverifiedEmail
{
    public function __construct(
        public readonly string $value,
    ) {
    }
}
