<?php

declare(strict_types=1);

namespace Be\Framework\SemanticVariables;

use Be\Framework\Attribute\Validate;
use Be\Framework\Fake\MyVendor\MyApp\SemanticTag\Teen;

final class Age
{
    #[Validate]
    public function validateAge(int $age): void
    {
        if ($age < 0) {
            throw new AgeNegativeException($age);
        }
        
        if ($age > 150) {
            throw new AgeTooHighException($age);
        }
    }

    #[Validate]
    public function validateTeen(#[Teen] int $age): void
    {
        // Base validation first
        $this->validateAge($age);

        if ($age < 13) {
            throw new TeenAgeTooYoungException($age);
        }

        if ($age > 19) {
            throw new TeenAgeTooOldException($age);
        }
    }
}