<?php

declare(strict_types=1);

namespace Be\Framework;

use Be\Framework\Attribute\Be;

#[Be(FakeProcessedData::class)]
final class TestSingleDestination
{
    public function __construct(
        public readonly string $data = 'test'
    ) {}
}