<?php

declare(strict_types=1);

namespace Ray\Framework;

use Ray\Framework\Be;
use Ray\InputQuery\Attribute\Input;

/**
 * Class that can become multiple different types (branching metamorphosis)
 */
#[Be([FakePremiumUser::class, FakeRegularUser::class])]
final class FakeBranchingInput
{
    public function __construct(
        #[Input] public readonly string $name,
        #[Input] public readonly string $email,
        #[Input] public readonly bool $isPremium = false
    ) {}
}