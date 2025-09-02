<?php

declare(strict_types=1);

namespace Be\Framework\SemanticLog\Context;

/**
 * Destination not found: Type matching failed or invalid #[Be] attribute
 */
final readonly class DestinationNotFound
{
    public function __construct(
        public string $error,
        public array $attemptedClasses = [],  // class-string[]
    ) {
    }
}
