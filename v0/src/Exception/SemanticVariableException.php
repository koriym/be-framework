<?php

declare(strict_types=1);

namespace Be\Framework\Exception;

use Be\Framework\SemanticVariable\Errors;
use DomainException;

use function array_map;
use function count;
use function implode;

/**
 * Thrown when semantic variable validation fails
 *
 * Wraps multiple semantic validation errors from the SemanticVariable system.
 * Provides access to all individual validation failures.
 */
final class SemanticVariableException extends DomainException
{
    public function __construct(
        private readonly Errors $errors,
    ) {
        $errorMessages = array_map(
            static fn ($exception) => $exception->getMessage(),
            $errors->exceptions,
        );

        $message = count($errorMessages) === 1
            ? $errorMessages[0]
            : 'Multiple semantic validation errors: ' . implode(', ', $errorMessages);

        parent::__construct($message);
    }

    /**
     * Get all semantic validation errors
     */
    public function getErrors(): Errors
    {
        return $this->errors;
    }
}
