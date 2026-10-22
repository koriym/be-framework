<?php

declare(strict_types=1);

namespace Be\Framework\SemanticVariable;

use Be\Framework\Types;
use Exception;

use function array_key_exists;
use function count;

/**
 * Collection of validation errors
 *
 * Immutable container for multiple validation exceptions with
 * multilingual message support.
 *
 * @psalm-import-type ExceptionCollection from Types
 * @psalm-import-type ValidationMessages from Types
 */
class Errors
{
    /**
     * @param ExceptionCollection $exceptions
     * @param list<string>        $variables  Semantic variable (or comma-joined constructor parameter)
     *                                         name for the exception at the same index. Populated by
     *                                         SemanticValidator; empty when a caller builds Errors
     *                                         directly without tracking provenance (legacy call sites,
     *                                         tests).
     * @param list<mixed>         $values     Value rejected by validation for the exception at the same
     *                                         index. Present only for single-value validations — an index
     *                                         without a tracked value (e.g. cross-field validation spanning
     *                                         multiple values) is simply absent; see {@see self::hasValueAt()}.
     * @param list<bool>          $sensitive  Whether the value at the same index came from a constructor
     *                                         parameter marked #[\SensitiveParameter]; true tells the log
     *                                         layer to redact it instead of recording it verbatim.
     * @phpstan-param array<Exception> $exceptions
     */
    public function __construct(
        public readonly array $exceptions,
        public readonly array $variables = [],
        public readonly array $values = [],
        public readonly array $sensitive = [],
    ) {
    }

    /**
     * Get error messages in specified locale
     *
     * @return ValidationMessages
     * @phpstan-return array<string>
     */
    public function getMessages(string $locale = 'en'): array
    {
        $handler = new ValidationMessageHandler();

        return $handler->getMessagesForExceptions($this->exceptions, $locale);
    }

    /**
     * Check if there are any errors
     *
     * @psalm-mutation-free
     */
    public function hasErrors(): bool
    {
        return ! empty($this->exceptions);
    }

    /**
     * Get the number of errors
     *
     * @psalm-mutation-free
     */
    public function count(): int
    {
        return count($this->exceptions);
    }

    /** Variable name tracked for the exception at $index, or null when not tracked. */
    public function variableAt(int $index): string|null
    {
        return $this->variables[$index] ?? null;
    }

    /** Whether a rejected value was tracked for the exception at $index. */
    public function hasValueAt(int $index): bool
    {
        return array_key_exists($index, $this->values);
    }

    /** Rejected value tracked for the exception at $index, or null when not tracked. */
    public function valueAt(int $index): mixed
    {
        return $this->values[$index] ?? null;
    }

    /** Whether the value at $index came from a #[\SensitiveParameter] constructor parameter. */
    public function isSensitiveAt(int $index): bool
    {
        return $this->sensitive[$index] ?? false;
    }

    /**
     * Combine with another Errors instance
     */
    public function combine(self $other): self
    {
        return new self(
            [...$this->exceptions, ...$other->exceptions],
            [...$this->variables, ...$other->variables],
            [...$this->values, ...$other->values],
            [...$this->sensitive, ...$other->sensitive],
        );
    }
}
