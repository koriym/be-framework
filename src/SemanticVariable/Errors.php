<?php

declare(strict_types=1);

namespace Be\Framework\SemanticVariable;

use Be\Framework\Types;
use Exception;

use function array_pad;
use function array_slice;
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
     * @phpstan-param array<Exception> $exceptions
     */
    public function __construct(
        public readonly array $exceptions,
        public readonly array $variables = [],
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

    /**
     * Combine with another Errors instance
     */
    public function combine(self $other): self
    {
        return new self(
            [...$this->exceptions, ...$other->exceptions],
            [...$this->alignedVariables(), ...$other->alignedVariables()],
        );
    }

    /**
     * One variable name per exception, so indexes still line up after a merge with an
     * instance built without names (`new Errors($exceptions)`).
     *
     * @return list<string>
     */
    private function alignedVariables(): array
    {
        $count = count($this->exceptions);

        return array_pad(array_slice($this->variables, 0, $count), $count, 'unknown');
    }
}
