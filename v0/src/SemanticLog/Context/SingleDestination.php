<?php

declare(strict_types=1);

namespace Be\Framework\SemanticLog\Context;

/**
 * Single destination: Object has #[Be(SomeClass::class)]
 */
final readonly class SingleDestination
{
    public function __construct(
        public string $nextClass,  // class-string
    ) {}
}