<?php

declare(strict_types=1);

namespace Be\Framework\Tests\Psalm\Fixture\Valid;

use Ray\Di\Di\Inject;
use Ray\InputQuery\Attribute\Input;

interface StorageInterface
{
}

final readonly class GoodBeing
{
    public function __construct(
        #[Input]
        public string $recordedAt,
        #[Inject]
        public StorageInterface $storage,
    ) {
    }
}
