<?php

declare(strict_types=1);

namespace Be\Framework\Tests\Psalm\Fixture\Invalid;

use Be\Framework\Attribute\Validate;
use DomainException;
use LogicException;

final class BadValidatorConditionalThrow
{
    #[Validate]
    public function validate(int $score): void
    {
        if ($score < 0) {
            throw new DomainException('negative score'); // OK
        }

        if ($score > 1000) {
            // Bug: LogicException is not DomainException.
            throw new LogicException('score too large');
        }
    }
}
