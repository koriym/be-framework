<?php

declare(strict_types=1);

namespace Be\Framework;

use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use Ray\InputQuery\Attribute\Input;
use SensitiveParameter;

final class SensitiveInjectTarget
{
    public function __construct(
        #[Input] public readonly string $username,
        #[Inject]
        #[Named('api_token')]
        #[SensitiveParameter]
        public readonly string $apiToken,
    ) {
    }
}
