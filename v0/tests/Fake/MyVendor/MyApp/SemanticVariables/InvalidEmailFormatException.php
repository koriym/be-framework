<?php

declare(strict_types=1);

namespace MyVendor\MyApp\SemanticVariables;

use Be\Framework\Attribute\Message;
use DomainException;

#[Message([
    'en' => 'Invalid email format: {email}',
    'ja' => '無効なメール形式: {email}',
    'es' => 'Formato de correo inválido: {email}',
])]
final class InvalidEmailFormatException extends DomainException
{
    public function __construct(
        public readonly string $email
    ) {
        parent::__construct("Invalid email format: {$email}");
    }
}