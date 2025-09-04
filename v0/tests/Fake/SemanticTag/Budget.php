<?php

declare(strict_types=1);

namespace Be\Framework\SemanticTag;

use Attribute;
use Be\Framework\Attribute\SemanticTag;

/**
 * Marks a parameter as requiring budget price validation (low-tier pricing)
 *
 * This SemanticTag provides hierarchical semantic validation:
 * - Base contract: Variable name implies basic validation (e.g., $price)
 * - SemanticTag constraint: #[Budget] narrows the meaning to budget price range
 *
 * Example:
 *   #[Budget] $price  // Validates as budget price (≤50 currency units)
 */
#[SemanticTag(description: "Price tier for budget products (≤50 currency units)")]
#[Attribute(Attribute::TARGET_PARAMETER)]
final class Budget
{
}