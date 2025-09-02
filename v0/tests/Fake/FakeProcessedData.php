<?php

declare(strict_types=1);

namespace Be\Framework;

use Ray\InputQuery\Attribute\Input;

final class FakeProcessedData
{
    public function __construct(
        #[Input] string $data
    ) {
        // Valid Be Framework class with proper Input attribute
    }
}