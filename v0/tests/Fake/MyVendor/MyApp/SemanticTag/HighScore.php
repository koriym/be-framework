<?php

declare(strict_types=1);

namespace Be\Framework\Fake\MyVendor\MyApp\SemanticTag;

use Attribute;
use Be\Framework\Attribute\SemanticTag;

/**
 * Marks a parameter as requiring high score validation (exceptional achievement level)
 *
 * This SemanticTag provides hierarchical semantic validation:
 * - Base contract: Variable name implies basic validation (e.g., $score)
 * - SemanticTag constraint: #[HighScore] narrows the meaning to high achievement range
 *
 * Example:
 *   #[HighScore] $game_score  // Validates as high score (10000+ points)
 */
#[SemanticTag(description: "High achievement level score (10000+ points)")]
#[Attribute(Attribute::TARGET_PARAMETER)]
final class HighScore
{
}