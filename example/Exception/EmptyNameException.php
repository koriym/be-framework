<?php

declare(strict_types=1);

namespace Be\Example\Exception;

use Be\Framework\Attribute\Message;
use DomainException;

/**
 * Semantic validation exception for empty name
 */
#[Message([
    'en' => 'Name cannot be empty.',
    'ja' => '名前は空にできません。'
])]
final class EmptyNameException extends DomainException
{
}
