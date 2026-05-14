<?php

declare(strict_types=1);

namespace Be\Framework\Tests\Psalm\Fixture\Invalid;

use Ray\InputQuery\Attribute\Input;

interface WeightStorageInterface
{
}

final readonly class BadBeingMissingInputAndInject
{
    public function __construct(
        #[Input]
        public string $recordedAt,
        // Bug: missing #[Input] / #[Inject] — runtime crash.
        public WeightStorageInterface $storage,
    ) {
    }
}
