<?php

declare(strict_types=1);

namespace Be\Framework\SemanticLog;

use Be\Framework\Exception\SemanticVariableException;
use Be\Framework\Types;
use Throwable;

use function array_values;

/**
 * Builds the per-variable `errors` list and throw-site location for an error close context.
 *
 * Split out of Logger: reading a SemanticVariableException's Errors is a self-contained
 * concern that doesn't touch logger state, kept separate to keep Logger's own complexity
 * in check.
 *
 * @psalm-import-type ValidationErrorList from Types
 */
final class ValidationErrorExtractor
{
    /**
     * Per-variable semantic validation failures carried by a SemanticVariableException (or
     * subtype); null for any other exception, so an error close without semantic validation
     * context simply omits the `errors` key.
     *
     * The rejected value itself is deliberately not recorded here: it is redundant with the
     * `prop` payload the step that produced it already logged (subject to the same
     * #[SensitiveParameter] filtering), and keeping a second, unredacted copy on
     * SemanticVariableException::getErrors() would put raw secrets in memory wherever an
     * error handler dumps the exception.
     *
     * @return ValidationErrorList|null
     */
    public function extract(Throwable $exception): array|null
    {
        if (! $exception instanceof SemanticVariableException) {
            return null;
        }

        $errors = $exception->getErrors();
        $entries = [];

        // array_values(): $errors->exceptions is typed array<Exception>, but every producer
        // in SemanticValidator builds it (and the parallel variables array) as a list —
        // forcing int keys here keeps $index usable against variableAt().
        foreach (array_values($errors->exceptions) as $index => $validationException) {
            $entries[] = [
                'variable' => $errors->variableAt($index) ?? 'unknown',
                'error' => $validationException::class,
                'message' => $validationException->getMessage(),
                'file' => $validationException->getFile(),
                'line' => $validationException->getLine(),
            ];
        }

        return $entries === [] ? null : $entries;
    }

    /**
     * Where an exception was thrown. A SemanticVariableException (or subtype) is always
     * constructed at a fixed point inside the framework (BecomingArguments::be(), or wherever
     * an application re-throws one after calling the validator directly) — that location
     * carries no diagnostic value. The real throw site is the first failing #[Validate]
     * method, captured as the first entry in its Errors, so we point there instead.
     *
     * @return array{file: string, line: int}
     */
    public function throwSite(Throwable $exception): array
    {
        if ($exception instanceof SemanticVariableException) {
            // Same normalization as extract(): `exceptions` is array<Exception>, not a list, and
            // this runs on the error path — a missing [0] here would replace the real failure.
            $first = array_values($exception->getErrors()->exceptions)[0] ?? null;
            if ($first !== null) {
                return ['file' => $first->getFile(), 'line' => $first->getLine()];
            }
        }

        return ['file' => $exception->getFile(), 'line' => $exception->getLine()];
    }
}
