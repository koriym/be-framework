<?php

declare(strict_types=1);

namespace Ray\Framework;

use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use Ray\InputQuery\Attribute\Input;

/**
 * Class that uses #[Named] for named DI bindings
 */
final class FakeWithNamed
{
    public function __construct(
        #[Input] public readonly string $input,
        #[Inject, Named('debug')] public readonly string $logLevel
    ) {}
}