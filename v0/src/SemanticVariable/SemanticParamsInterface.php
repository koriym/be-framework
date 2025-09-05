<?php

declare(strict_types=1);

namespace Be\Framework\SemanticVariable;

/**
 * Interface for parameter collection validation
 *
 * Implements hierarchical semantic validation where variable names provide
 * base validation contracts and SemanticTag attributes add constraints:
 * - `$age` → validates basic age rules
 * - `#[Teen] $age` → validates age + teen-specific constraints
 * - `#[Adult] $age` → validates age + adult-specific constraints
 *
 * @link koriym.github.io/be-framework/v0/docs/reference/semantic-variables.html
 * @see SemanticValidator For the underlying validation engine
 * @see SemanticTag For attribute-based constraints
 */
interface SemanticParamsInterface
{
    /**
     * Validate all parameters with given values
     */
    public function validate(array $values): Errors;
}
