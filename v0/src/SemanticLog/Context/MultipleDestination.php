<?php

declare(strict_types=1);

namespace Be\Framework\SemanticLog\Context;

/**
 * Multiple destinations: Object has #[Be([ClassA::class, ClassB::class])]
 */
final class MultipleDestination
{
    public function __construct(
        public readonly array $possibleClasses,  // class-string[]
    ) {}
}
