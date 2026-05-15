<?php

declare(strict_types=1);

namespace Be\Framework\Tests\Psalm\Fixture\Valid;

use Ray\InputQuery\Attribute\Input;

interface SuppressedStorageInterface
{
}

/** @psalm-suppress MissingBeingParameterAttribute */
final readonly class SuppressedBeing
{
    public function __construct(
        #[Input]
        public string $recordedAt,
        public SuppressedStorageInterface $storage,
    ) {
    }
}
