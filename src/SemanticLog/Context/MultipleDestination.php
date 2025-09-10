<?php

declare(strict_types=1);

namespace Be\Framework\SemanticLog\Context;

use Be\Framework\Types;

/**
 * Multiple destinations: Object has #[Be([ClassA::class, ClassB::class])]
 *
 * @psalm-import-type QualifiedClasses from Types
 */
final class MultipleDestination
{
    /** @param QualifiedClasses $possibleClasses */
    public function __construct(
        public readonly array $possibleClasses,
    ) {
    }
}
