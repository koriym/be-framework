<?php

declare(strict_types=1);

namespace Be\Framework\Attribute;

use Attribute;

/**
 * Attribute for internationalized message definitions
 *
 * Provides multi-language message support for exceptions and other components.
 * Supports array of translations keyed by language code.
 *
 * Examples:
 *   #[Message(['en' => 'Error message', 'ja' => 'エラーメッセージ'])]
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final class Message
{
    /** @param array<string, string> $messages Locale => message template mapping */
    public function __construct(
        public readonly array $messages,
    ) {
    }
}
