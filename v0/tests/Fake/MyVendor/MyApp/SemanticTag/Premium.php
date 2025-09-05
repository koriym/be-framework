<?php

declare(strict_types=1);

namespace Be\Framework\Fake\MyVendor\MyApp\SemanticTag;

use Attribute;
use Be\Framework\Attribute\SemanticTag;

/**
 * Marks a parameter as requiring premium price validation (high-tier pricing)
 *
 * This SemanticTag provides hierarchical semantic validation:
 * - Base contract: Variable name implies basic validation (e.g., $price)
 * - SemanticTag constraint: #[Premium] narrows the meaning to premium price range
 *
 * Example:
 *   #[Premium] $price  // Validates as premium price (100+ currency units)
 */
#[SemanticTag(description: "Price tier for premium products (100+ currency units)")]
#[Attribute(Attribute::TARGET_PARAMETER)]
final class Premium
{
}