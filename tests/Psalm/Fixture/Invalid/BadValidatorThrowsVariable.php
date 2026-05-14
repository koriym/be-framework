<?php

declare(strict_types=1);

namespace Be\Framework\Tests\Psalm\Fixture\Invalid;

use Be\Framework\Attribute\Validate;
use RuntimeException;

final class BadValidatorThrowsVariable
{
    #[Validate]
    public function validate(string $value): void
    {
        if ($value === '') {
            // Bug: variable holds RuntimeException, not DomainException.
            $error = new RuntimeException('empty value');

            throw $error;
        }
    }
}
