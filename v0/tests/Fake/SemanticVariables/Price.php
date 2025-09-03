<?php

declare(strict_types=1);

namespace Be\Framework\SemanticVariables;

use Be\Framework\Attribute\Validate;
use DomainException;

final class Price
{
    #[Validate]
    public function validateAmount(float $price): void
    {
        if ($price < 0) {
            throw new DomainException("Price cannot be negative: {$price}");
        }
    }
}