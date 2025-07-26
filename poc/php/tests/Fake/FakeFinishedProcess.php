<?php

declare(strict_types=1);

namespace Be\Framework;

use Ray\InputQuery\Attribute\Input;

/**
 * Final class that receives the result object from previous step
 */
final class FakeFinishedProcess
{
    public function __construct(
        #[Input] public readonly FakeResult $result,
        #[Input] public readonly string $input
    ) {}
}