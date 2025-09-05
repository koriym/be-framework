<?php

declare(strict_types=1);

namespace Be\Framework\Fake\MyVendor\MyApp\SemanticTag;

use Attribute;
use Be\Framework\Attribute\SemanticTag;

/**
 * Marks a parameter as requiring personal best validation (individual achievement level)
 *
 * This SemanticTag provides hierarchical semantic validation:
 * - Base contract: Variable name implies basic validation (e.g., $score)
 * - SemanticTag constraint: #[PersonalBest] narrows the meaning to personal achievement range
 *
 * Example:
 *   #[PersonalBest] $game_score  // Validates as personal best (1000+ points)
 */
#[SemanticTag(description: "Individual achievement level score (1000+ points)")]
#[Attribute(Attribute::TARGET_PARAMETER)]
final class PersonalBest
{
}