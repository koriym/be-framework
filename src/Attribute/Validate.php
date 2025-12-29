<?php

declare(strict_types=1);

namespace Be\Framework\Attribute;

use Attribute;

/**
 * Marks a method as a semantic validation method
 *
 * When applied to a method, the SemanticValidator will invoke this method
 * to perform custom validation logic during semantic variable validation.
 *
 * The method parameters determine which semantic variables it validates.
 * Parameter names must match the semantic variable names being validated.
 *
 * Example:
 * ```php
 * #[Validate]
 * public function validateAge(int $age): bool
 * {
 *     return $age >= 0 && $age <= 150;
 * }
 * ```
 *
 * @see \Be\Framework\SemanticVariable\SemanticValidator
 */
#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_METHOD)]
final class Validate
{
}
