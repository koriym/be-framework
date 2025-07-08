<?php

declare(strict_types=1);

namespace Ray\Framework;

use Ray\Framework\Be;
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