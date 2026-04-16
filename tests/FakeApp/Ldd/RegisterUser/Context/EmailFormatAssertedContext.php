<?php

declare(strict_types=1);

namespace MyVendor\MyApp\Ldd\RegisterUser\Context;

use Koriym\SemanticLogger\AbstractContext;

final class EmailFormatAssertedContext extends AbstractContext
{
    public const string TYPE = 'email_format_asserted';
    public const string SCHEMA_URL = 'https://myvendor.example.com/schemas/email-format-asserted.json';

    public function __construct(
        public readonly string $email,
    ) {
    }
}
