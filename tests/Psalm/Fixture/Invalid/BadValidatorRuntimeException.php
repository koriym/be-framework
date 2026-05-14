<?php

declare(strict_types=1);

namespace Be\Framework\Tests\Psalm\Fixture\Invalid;

use Be\Framework\Attribute\Validate;
use RuntimeException;

final class InvalidWeightException extends RuntimeException
{
}

final class BadValidatorRuntimeException
{
    #[Validate]
    public function validate(float $weightKg): void
    {
        if ($weightKg < 0) {
            // Bug: extends RuntimeException, not DomainException — silently propagates.
            throw new InvalidWeightException("Negative weight: {$weightKg}");
        }
    }
}
