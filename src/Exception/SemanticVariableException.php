<?php

declare(strict_types=1);

namespace Be\Framework\Exception;

use Be\Framework\SemanticVariable\Errors;
use DomainException;
use Throwable;

use function array_map;
use function count;
use function implode;

/**
 * Thrown when semantic variable validation fails
 *
 * Wraps multiple semantic validation errors from the SemanticVariable system.
 * Provides access to all individual validation failures.
 *
 * This is the common base type. Depending on where in the metamorphosis chain
 * validation failed, {@see Becoming} refines it into a more specific subtype:
 * - {@see InputSemanticVariableException} for the first metamorphosis (input error)
 * - {@see RuntimeSemanticVariableException} for any later metamorphosis (runtime error)
 *
 * Catching this base type still catches both subtypes.
 */
class SemanticVariableException extends DomainException
{
    public function __construct(
        private readonly Errors $errors,
        Throwable|null $previous = null,
    ) {
        $errorMessages = array_map(
            static fn ($exception) => $exception->getMessage(),
            $errors->exceptions,
        );

        $message = count($errorMessages) === 1
            ? $errorMessages[0]
            : 'Multiple semantic validation errors: ' . implode(', ', $errorMessages);

        parent::__construct($message, 0, $previous);
    }

    /**
     * Get all semantic validation errors
     */
    public function getErrors(): Errors
    {
        return $this->errors;
    }
}
