<?php

declare(strict_types=1);

namespace MyVendor\MyApp\Ldd\RegisterUser\Context;

use Koriym\SemanticLogger\AbstractContext;

final class UserInsertedContext extends AbstractContext
{
    public const string TYPE = 'user_inserted';
    public const string SCHEMA_URL = 'https://myvendor.example.com/schemas/user-inserted.json';

    public function __construct(
        public readonly int $userId,
        public readonly string $email,
    ) {
    }
}
