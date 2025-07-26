<?php

declare(strict_types=1);

namespace Be\Framework;

use Be\Framework\Be;
use Ray\InputQuery\Attribute\Input;

/**
 * Processing step that creates a result object and declares next transformation
 */
#[Be(FakeFinishedProcess::class)]
final class FakeProcessingStep
{
    public readonly FakeResult $result;

    public function __construct(
        #[Input] public readonly string $input
    ) {
        // Create result object based on input
        $this->result = new FakeResult($input, strlen($input) > 3);
    }
}