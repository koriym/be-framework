<?php

declare(strict_types=1);

namespace Be\Framework\SemanticLog\Context;

use Be\Framework\Types;
use JsonSerializable;
use Koriym\SemanticLogger\AbstractContext;
use Override;

/**
 * Close context: the metamorphosis failed with a thrown exception.
 *
 * @psalm-import-type ValidationErrorList from Types
 */
final class BeingErrorCloseContext extends AbstractContext implements JsonSerializable
{
    public const string TYPE = 'being_error_close';

    public const string SCHEMA_URL = 'https://be-framework.org/schemas/being-error-close.json';

    /**
     * @param string                   $error   Fully-qualified class name of the thrown exception
     *                                          (or a sentinel string such as "UnknownError" when no Throwable is available)
     * @param string                   $message Exception message
     * @param ValidationErrorList|null $errors  Per-variable semantic validation failures when
     *                         $error is a SemanticVariableException (or subtype); null for any
     *                         other exception type.
     * @param string                   $file    Absolute path to the file the exception was thrown from.
     * @param int                      $line    Line number within $file.
     */
    public function __construct(
        public readonly string $error,
        public readonly string $message,
        public readonly array|null $errors = null,
        public readonly string $file = '',
        public readonly int $line = 0,
    ) {
    }

    /** @return array<string, mixed> */
    #[Override]
    public function jsonSerialize(): array
    {
        $payload = [
            'error' => $this->error,
            'message' => $this->message,
        ];

        if ($this->errors !== null) {
            $payload['errors'] = $this->errors;
        }

        $payload['file'] = $this->file;
        $payload['line'] = $this->line;

        return $payload;
    }
}
