<?php

declare(strict_types=1);

namespace Be\Framework\SemanticVariable;

use ReflectionMethod;
use ReflectionParameter;

/**
 * Interface for semantic variable validation
 *
 * Provides two distinct APIs:
 * - validateArgs: Framework usage for method-wide validation
 * - validateArg: Test usage for individual parameter validation
 */
interface SemanticValidatorInterface
{
    /**
     * Validate all arguments for a method (primary API)
     *
     * @param ReflectionMethod $method Method containing parameter definitions
     * @param array            $args   Values to validate (associative array: param_name => value)
     *
     * @return Errors Validation errors (empty if validation passes)
     */
    public function validateArgs(ReflectionMethod $method, array $args): Errors;

    /**
     * Validate single parameter (test convenience API)
     *
     * @param ReflectionParameter $parameter Parameter containing variable name and attributes
     * @param mixed               $value     Value to validate
     *
     * @return Errors Validation errors (empty if validation passes)
     */
    public function validateArg(ReflectionParameter $parameter, mixed $value): Errors;
}
