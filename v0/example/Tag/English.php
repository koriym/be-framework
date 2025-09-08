<?php

declare(strict_types=1);

namespace Be\Example\Tag;

use Attribute;
use Be\Framework\Attribute\SemanticTag;

/**
 * English name validation
 *
 * Validates names using English language constraints (ASCII letters and spaces only).
 *
 * @link https://schema.org/Language Language schema
 * @see https://schema.org/inLanguage
 */
#[SemanticTag('English')]
#[Attribute(Attribute::TARGET_PARAMETER)]
final class English
{
}
