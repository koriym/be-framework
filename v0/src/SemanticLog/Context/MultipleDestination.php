<?php

declare(strict_types=1);

namespace Be\Framework\SemanticLog\Context;

/**
 * Multiple destinations: Object has #[Be([ClassA::class, ClassB::class])]
 */
final readonly class MultipleDestination
{
    public function __construct(
        public array $possibleClasses,  // class-string[]
    ) {
    }
}
