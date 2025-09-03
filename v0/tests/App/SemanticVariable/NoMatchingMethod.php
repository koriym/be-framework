<?php

declare(strict_types=1);

namespace Be\App\SemanticVariable;

use Be\Framework\Attribute\Validate;

final class NoMatchingMethod
{
    // This method has #[Validate] but expects 2 args, so won't match single arg calls

    #[Validate]
    public function validateTwoArgs(string $arg1, string $arg2): void
    {
        // Validation logic
    }

    // This method has no #[Validate] attribute
    public function someOtherMethod(string $arg): void
    {
        // Not a validation method
    }
}
