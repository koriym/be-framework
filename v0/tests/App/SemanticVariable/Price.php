<?php

declare(strict_types=1);

namespace Be\App\SemanticVariable;

use Be\Framework\Attribute\Validate;
use DomainException;

final class Price
{
    #[Validate]
    public function validatePrice(float $price): void
    {
        if ($price < 0) {
            throw new DomainException("Price cannot be negative: {$price}");
        }
    }
}
