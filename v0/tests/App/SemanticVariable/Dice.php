<?php

declare(strict_types=1);

namespace Be\App\SemanticVariable;

use Be\Framework\Attribute\Validate;
use DomainException;

final class Dice
{
    #[Validate]
    public function validateSides(int $dice): void
    {
        if ($dice < 1 || $dice > 6) {
            throw new DomainException("Dice value must be between 1 and 6: {$dice}");
        }
    }
}
