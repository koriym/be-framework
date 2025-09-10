<?php

declare(strict_types=1);

namespace MyVendor\MyApp\SemanticTag;

use Attribute;
use Be\Framework\Attribute\SemanticTag;

/**
 * Marks a parameter as requiring adult age validation (18+ years)
 *
 * This SemanticTag provides hierarchical semantic validation:
 * - Base contract: Variable name implies basic validation (e.g., $age)
 * - SemanticTag constraint: #[Adult] narrows the meaning to adult age range
 *
 * Example:
 *   #[Adult] $age  // Validates as adult age (18+ years)
 */
#[SemanticTag(description: 'Age constraint for adults (18+ years)')]
#[Attribute(Attribute::TARGET_PARAMETER)]
final class Adult
{
}
