<?php

declare(strict_types=1);

namespace Be\Example\Semantic;

use Be\Example\Exception\StyleException;
use Be\Framework\Attribute\Validate;

use function in_array;

/**
 * Greeting style
 *
 * Validates communication style values, accepting only 'formal' or 'casual'.
 *
 * @link https://schema.org/CommunicationStyle Communication style schema
 * @link https://schema.org/interactionStyle Interaction style property
 * @see https://schema.org/tone
 * @see https://schema.org/audience
 */
final class Style
{
    #[Validate]
    public function validate(string $style): void
    {
        if (! in_array($style, ['formal', 'casual'], true)) {
            throw new StyleException($style);
        }
    }
}
