<?php

declare(strict_types=1);

namespace Be\Framework\Attribute;

use Attribute;

/**
 * Marks a parameter as requiring teen age validation (13-19 years)
 *
 * This SemanticTag provides hierarchical semantic validation:
 * - Base contract: Variable name implies basic validation (e.g., $age)
 * - SemanticTag constraint: #[Teen] narrows the meaning to teenage range
 *
 * Example:
 *   #[Teen] $age  // Validates as teen age (13-19 years)
 */
#[SemanticTag(description: "Age constraint for teenagers (13-19 years)")]
#[Attribute(Attribute::TARGET_PARAMETER)]
final class Teen
{
}
