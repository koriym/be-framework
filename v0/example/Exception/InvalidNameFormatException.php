<?php

declare(strict_types=1);

namespace Be\Example\Exception;

use Be\Framework\Attribute\Message;
use DomainException;

/**
 * Semantic validation exception for invalid name format
 */
#[Message([
    'en' => 'Name must contain only letters and spaces, got "{name}"',
    'ja' => '名前は文字とスペースのみを含む必要があります。"{name}"が指定されました'
])]
final class InvalidNameFormatException extends DomainException
{
    public function __construct(public readonly string $name)
    {
        parent::__construct("Invalid name format: {$name}");
    }
}
