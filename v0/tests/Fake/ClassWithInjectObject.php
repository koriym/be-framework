<?php

declare(strict_types=1);

namespace Be\Framework;

use Ray\Di\Di\Inject;
use stdClass;

final class ClassWithInjectObject
{
    public function __construct(
        public readonly string $data,
        #[Inject] public readonly stdClass $injectedObject,
        #[Inject] public readonly string $missingParam = 'default', // This param won't exist in args
    ) {
    }
}