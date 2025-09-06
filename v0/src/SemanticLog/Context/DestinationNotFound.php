<?php

declare(strict_types=1);

namespace Be\Framework\SemanticLog\Context;

use Be\Framework\Types;

/**
 * Destination not found: Type matching failed or invalid #[Be] attribute
 *
 * @psalm-import-type AttemptedClasses from Types
 */
final readonly class DestinationNotFound
{
    /** @param AttemptedClasses $attemptedClasses */
    public function __construct(
        public string $error,
        public array $attemptedClasses = [],
    ) {
    }
}
