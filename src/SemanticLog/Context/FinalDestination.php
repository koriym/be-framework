<?php

declare(strict_types=1);

namespace Be\Framework\SemanticLog\Context;

/**
 * Final destination: Object has no #[Be] attribute, metamorphosis complete
 */
final readonly class FinalDestination
{
    public function __construct(
        public string $finalClass,  // class-string
    ) {
    }
}
