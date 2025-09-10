<?php

declare(strict_types=1);

namespace Be\Framework\SemanticVariable;

use Be\Framework\Types;
use Exception;

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
     * @phpstan-param array<Exception> $exceptions
     */
    public function __construct(
        public readonly array $exceptions,
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

    /**
     * Combine with another Errors instance
     */
    public function combine(self $other): self
    {
        return new self([...$this->exceptions, ...$other->exceptions]);
    }
}
