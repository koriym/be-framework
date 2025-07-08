<?php

declare(strict_types=1);

namespace Ray\Framework;

use Ray\Framework\Be;
use Ray\InputQuery\Attribute\Input;

/**
 * Class that branches to classes that will all fail instantiation
 */
#[Be([FakeFailingUserA::class, FakeFailingUserB::class])]
final class FakeFailingBranch
{
    public function __construct(
        #[Input] public readonly string $name
    ) {}
}