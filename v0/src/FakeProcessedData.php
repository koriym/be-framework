<?php

declare(strict_types=1);

namespace Be\Framework;

use Ray\InputQuery\Attribute\Input;

/**
 * Fake processed data class for testing
 */
final class FakeProcessedData
{
    public function __construct(
        #[Input]
        public readonly string $data,
    ) {
    }
}
