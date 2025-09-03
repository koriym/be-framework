<?php

declare(strict_types=1);

namespace Be\Framework\Attribute;

use Attribute;

/**
 * Multilingual message attribute for validation exceptions
 *
 * Provides default messages in multiple languages for validation failures.
 * Messages support template interpolation using {property} syntax.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class Message
{
    /** @param array<string, string> $messages Locale => message template mapping */
    public function __construct(
        public readonly array $messages = [],
    ) {
    }
}
