<?php

declare(strict_types=1);

namespace Be\Framework;

use Be\Framework\Be;
use Ray\InputQuery\Attribute\Input;

/**
 * Starting point for object property inheritance test
 */
#[Be(FakeProcessingStep::class)]
final class FakeInputData
{
    public function __construct(
        #[Input] public readonly string $input
    ) {}
}