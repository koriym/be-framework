<?php

declare(strict_types=1);

namespace Be\Framework\Tests\Fake;

use Ray\InputQuery\Attribute\Input;
use Ray\Di\Di\Inject;

/**
 * Test constructor class with inject parameters
 */
final readonly class TestPersonWithInject
{
    public function __construct(
        #[Input] public string $name,
        #[Input] public int $age,
        #[Inject] public \stdClass $service
    ) {}
}