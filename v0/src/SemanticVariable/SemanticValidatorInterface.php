<?php

declare(strict_types=1);

namespace Be\Framework\SemanticVariable;

/**
 * Interface for semantic variable validation
 *
 * Provides contract for validating semantic variables based on their names
 * and parameter attributes for hierarchical validation.
 */
interface SemanticValidatorInterface
{
    /**
     * Validate semantic variable with given arguments
     */
    public function validate(string $variableName, mixed ...$args): Errors;

    /**
     * Validate semantic variable with parameter attributes for hierarchical validation
     *
     * @param string $variableName        Variable name for basic semantic validation
     * @param array  $parameterAttributes Parameter attributes for hierarchical validation
     * @param mixed  ...$args             Arguments to validate
     */
    public function validateWithAttributes(string $variableName, array $parameterAttributes = [], mixed ...$args): Errors;

    /**
     * Validate semantic variables and throw exception if errors found
     *
     * Throws SemanticVariableException with detailed error information if validation fails.
     * This preserves all validation errors and their messages for proper error handling.
     */
    public function validateAndThrow(string $variableName, mixed ...$args): void;

    /**
     * Validate all semantic variables in an object
     */
    public function validateObject(object $object): Errors;
}
