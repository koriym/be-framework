<?php

declare(strict_types=1);

namespace Ray\Framework;

use Ray\Di\Di\Inject;
use Ray\InputQuery\Attribute\Input;

/**
 * Class that uses #[Inject] for DI resolution
 */
final class FakeWithInject
{
    public function __construct(
        #[Input] public readonly string $input,
        #[Inject] public readonly FakeService $service
    ) {}
}