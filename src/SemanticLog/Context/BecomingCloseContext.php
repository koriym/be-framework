<?php

declare(strict_types=1);

namespace Be\Framework\SemanticLog\Context;

use Be\Framework\Types;
use JsonSerializable;
use Koriym\SemanticLogger\AbstractContext;
use Override;

/**
 * Close context for the whole metamorphosis chain.
 *
 * Emitted when the chain terminates — either because it reached a being with
 * no further #[Be], or because a transformation threw. Carries the chain exit
 * state plus either the terminal FQCN or the exception that ended the chain.
 *
 * @psalm-import-type ValidationErrorList from Types
 */
final class BecomingCloseContext extends AbstractContext implements JsonSerializable
{
    public const string TYPE = 'becoming_close';

    public const string SCHEMA_URL = 'https://be-framework.org/schemas/becoming-close.json';

    public const string EXIT_SUCCESS = 'success';

    public const string EXIT_ERROR = 'error';

    public const string ORIGIN_INPUT = 'input';

    public const string ORIGIN_RUNTIME = 'runtime';

    /**
     * @param string                   $exit    Exit status for the chain (self::EXIT_SUCCESS|self::EXIT_ERROR).
     * @param class-string|null        $final   FQCN of the terminal being, or null if the chain failed.
     * @param string|null              $error   FQCN of the thrown exception, or null on success.
     * @param string|null              $message Exception message, or null on success.
     * @param string|null              $origin  Provenance of a semantic validation failure
     *                                           (self::ORIGIN_INPUT|self::ORIGIN_RUNTIME), or null when
     *                                           the failure is not a semantic validation error.
     * @param ValidationErrorList|null $errors  Per-variable semantic validation failures when
     *                                     $error is a SemanticVariableException (or subtype);
     *                                     null for any other exception, or on success.
     * @param string|null              $file    Absolute path to the file the exception was thrown from,
     *                                           or null on success.
     * @param int|null                 $line    Line number within $file, or null on success.
     */
    public function __construct(
        public readonly string $exit,
        public readonly string|null $final = null,
        public readonly string|null $error = null,
        public readonly string|null $message = null,
        public readonly string|null $origin = null,
        public readonly array|null $errors = null,
        public readonly string|null $file = null,
        public readonly int|null $line = null,
    ) {
    }

    /** @return array<string, mixed> */
    #[Override]
    public function jsonSerialize(): array
    {
        $payload = ['exit' => $this->exit];

        if ($this->final !== null) {
            $payload['final'] = $this->final;
        }

        if ($this->error !== null) {
            $payload['error'] = $this->error;
            $payload['message'] = $this->message ?? '';

            if ($this->origin !== null) {
                $payload['origin'] = $this->origin;
            }

            if ($this->errors !== null) {
                $payload['errors'] = $this->errors;
            }

            $payload['file'] = $this->file ?? '';
            $payload['line'] = $this->line ?? 0;
        }

        return $payload;
    }
}
