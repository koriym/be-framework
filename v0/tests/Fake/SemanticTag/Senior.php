<?php

declare(strict_types=1);

namespace Be\Framework\SemanticTag;

use Attribute;
use Be\Framework\Attribute\SemanticTag;

/**
 * Marks a parameter as requiring senior age validation (65+ years)
 *
 * This SemanticTag provides hierarchical semantic validation:
 * - Base contract: Variable name implies basic validation (e.g., $age)
 * - SemanticTag constraint: #[Senior] narrows the meaning to senior age range
 *
 * Example:
 *   #[Senior] $age  // Validates as senior age (65+ years)
 */
#[SemanticTag(description: "Age constraint for seniors (65+ years)")]
#[Attribute(Attribute::TARGET_PARAMETER)]
final class Senior
{
}