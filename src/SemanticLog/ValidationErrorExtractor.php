<?php

declare(strict_types=1);

namespace Be\Framework\SemanticLog;

use Be\Framework\Exception\SemanticVariableException;
use Be\Framework\Types;
use Throwable;

use function array_values;
use function get_debug_type;
use function is_array;
use function is_scalar;

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
        // in SemanticValidator builds it (and the parallel variables/values/sensitive arrays)
        // as a list — forcing int keys here keeps $index usable against the other accessors.
        foreach (array_values($errors->exceptions) as $index => $validationException) {
            $entry = [
                'variable' => $errors->variableAt($index) ?? 'unknown',
                'error' => $validationException::class,
                'message' => $validationException->getMessage(),
                'file' => $validationException->getFile(),
                'line' => $validationException->getLine(),
            ];

            if ($errors->hasValueAt($index)) {
                /** @psalm-suppress MixedAssignment the rejected value is validated-input shaped data, not something we can narrow further */
                $value = $errors->isSensitiveAt($index) ? ObjectPropertyExtractor::FILTERED : $errors->valueAt($index);
                $entry['value'] = $this->normalizeValue($value);
            }

            $entries[] = $entry;
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
            $errors = $exception->getErrors();

            if ($errors->hasErrors()) {
                $first = $errors->exceptions[0];

                return ['file' => $first->getFile(), 'line' => $first->getLine()];
            }
        }

        return ['file' => $exception->getFile(), 'line' => $exception->getLine()];
    }

    /**
     * Reduce a rejected value to a JSON-safe shape: scalars, null and arrays pass through
     * unchanged; anything else (objects, resources) is recorded by its debug type, matching
     * how #[Inject] object values are summarized in Logger::extractTranscendentSources().
     */
    private function normalizeValue(mixed $value): array|bool|float|int|string|null
    {
        if ($value === null || is_scalar($value) || is_array($value)) {
            return $value;
        }

        return get_debug_type($value);
    }
}
