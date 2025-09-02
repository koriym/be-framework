<?php

declare(strict_types=1);

namespace Be\Framework;

use Ray\Di\Injector;

#[Be(\Be\Framework\FakeProcessedData::class)]
final class TestInputWithDependency
{
    public function __construct(
        public readonly string $data,
        public readonly Injector $injector
    ) {}
}