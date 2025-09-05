<?php

declare(strict_types=1);

namespace Be\Framework\SemanticVariable;

use ReflectionMethod;
use ReflectionParameter;

/**
 * Null object pattern implementation for semantic validation
 *
 * Performs no validation and always returns no errors.
 * Used when semantic validation is not needed or disabled.
 */
final class NullValidator implements SemanticValidatorInterface
{
    /**
     * Always returns no errors for method arguments
     */
    public function validateArgs(ReflectionMethod $method, array $args): Errors
    {
        return new NullErrors();
    }

    /**
     * Always returns no errors for single parameter
     */
    public function validateArg(ReflectionParameter $parameter, mixed $value): Errors
    {
        return new NullErrors();
    }

    /**
     * Legacy method: Always returns no errors (for backward compatibility)
     */
    public function validate(string $variableName, mixed ...$args): Errors
    {
        return new NullErrors();
    }

    /**
     * Legacy method: Always returns no errors
     */
    public function validateLegacy(string $variableName, mixed ...$args): Errors
    {
        return new NullErrors();
    }

    /**
     * Legacy method: Always returns no errors regardless of attributes
     */
    public function validateWithAttributes(string $variableName, array $parameterAttributes = [], mixed ...$args): Errors
    {
        return new NullErrors();
    }

    /**
     * Legacy method: Never throws exceptions - validation always passes
     */
    public function validateAndThrow(string $variableName, mixed ...$args): void
    {
        // No-op: null validator never throws
    }

    /**
     * Legacy method: Always returns no errors for object validation
     */
    public function validateObject(object $object): Errors
    {
        return new NullErrors();
    }
}
