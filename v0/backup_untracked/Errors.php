<?php

declare(strict_types=1);

namespace Be\Framework;

use Exception;

use function array_map;

/**
 * Collection of validation errors
 * 
 * Immutable container for multiple validation exceptions with 
 * multilingual message support.
 */
class Errors
{
    /**
     * @param array<Exception> $exceptions
     */
    public function __construct(
        public readonly array $exceptions
    ) {}
    
    /**
     * Get error messages in specified locale
     * 
     * @return array<string>
     */
    public function getMessages(string $locale = 'en'): array
    {
        $handler = new ValidationMessageHandler();
        return $handler->getMessagesForExceptions($this->exceptions, $locale);
    }
    
    /**
     * Check if there are any errors
     */
    public function hasErrors(): bool
    {
        return !empty($this->exceptions);
    }
    
    /**
     * Get the number of errors
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
