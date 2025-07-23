<?php

declare(strict_types=1);

namespace Be\Framework;

use Ray\InputQuery\Attribute\Input;

/**
 * Simple result object that will be passed as property
 */
final class FakeResult
{
    public function __construct(
        #[Input] public readonly string $value,
        #[Input] public readonly bool $isSuccess
    ) {}
}