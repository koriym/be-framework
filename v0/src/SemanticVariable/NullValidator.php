<?php

declare(strict_types=1);

namespace Be\Framework\SemanticVariable;

/**
 * Null object pattern implementation for semantic validation
 *
 * Performs no validation and always returns no errors.
 * Used when semantic validation is not needed or disabled.
 */
final class NullValidator implements SemanticValidatorInterface
{
    /**
     * Always returns no errors
     */
    public function validate(string $variableName, mixed ...$args): Errors
    {
        return new NullErrors();
    }

    /**
     * Always returns no errors regardless of attributes
     */
    public function validateWithAttributes(string $variableName, array $parameterAttributes = [], mixed ...$args): Errors
    {
        return new NullErrors();
    }

    /**
     * Never throws exceptions - validation always passes
     */
    public function validateAndThrow(string $variableName, mixed ...$args): void
    {
        // No-op: null validator never throws
    }

    /**
     * Always returns no errors for object validation
     */
    public function validateObject(object $object): Errors
    {
        return new NullErrors();
    }
}
