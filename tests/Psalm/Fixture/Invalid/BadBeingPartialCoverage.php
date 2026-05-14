<?php

declare(strict_types=1);

namespace Be\Framework\Tests\Psalm\Fixture\Invalid;

use Ray\Di\Di\Inject;
use Ray\InputQuery\Attribute\Input;

interface ClockInterface
{
}

final readonly class BadBeingPartialCoverage
{
    public function __construct(
        #[Input]
        public string $name,
        #[Inject]
        public ClockInterface $clock,
        // Bug: this one is missing both attributes.
        public int $age,
    ) {
    }
}
