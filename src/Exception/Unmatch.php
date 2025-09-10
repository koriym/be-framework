<?php

declare(strict_types=1);

namespace Be\Framework\Exception;

use function is_object;
use function is_string;
use function method_exists;
use function sprintf;

/**
 * Represents a structured unmatch for a specific candidate class during type matching
 */
final readonly class Unmatch
{
    public function __construct(
        public string $className,
        public UnmatchReason $reason,
        public mixed $details = null,
    ) {
    }

    /** @psalm-mutation-free */
    public function getMessage(): string
    {
        return match ($this->reason) {
            UnmatchReason::TypeMismatch => "Type mismatch in {$this->className}",
            UnmatchReason::Constructor => sprintf('Constructor error in %s: %s', $this->className, $this->formatDetails()),
            UnmatchReason::Validation => sprintf('Validation error in %s: %s', $this->className, $this->formatDetails()),
        };
    }

    /** @psalm-mutation-free */
    private function formatDetails(): string
    {
        if ($this->details === null) {
            return '';
        }

        if (is_string($this->details)) {
            return $this->details;
        }

        if (is_object($this->details) && method_exists($this->details, '__toString')) {
            return (string) $this->details;
        }

        return '[object details]';
    }
}
