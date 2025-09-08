<?php

declare(strict_types=1);

namespace Be\Example\Exception;

use Be\Framework\Attribute\Message;
use DomainException;

/**
 * Semantic validation exception for style
 */
#[Message([
    'en' => 'Style must be either "formal" or "casual", got {style}',
    'ja' => 'スタイルは"formal"または"casual"である必要があります。{style}が指定されました'
])]
final class StyleException extends DomainException
{
    public function __construct(public readonly string $style)
    {
        parent::__construct("Invalid style: {$style}");
    }
}
