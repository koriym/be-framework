<?php

declare(strict_types=1);

namespace Be\Framework;

use Ray\InputQuery\Attribute\Input;

/**
 * Class without #[Be] attribute - no metamorphosis
 */
final class FakeNoMetamorphosis
{
    public function __construct(
        #[Input] public readonly string $message
    ) {}
}