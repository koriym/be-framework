<?php

declare(strict_types=1);

namespace MyVendor\MyApp\SemanticVariables;

use Be\Framework\Attribute\Validate;
use DomainException;
use MyVendor\MyApp\SemanticTag\Budget;
use MyVendor\MyApp\SemanticTag\Premium;

final class ProductPrice
{
    #[Validate]
    public function validateProductPrice(float $price): void
    {
        if ($price < 0.0) {
            throw new DomainException("Product price cannot be negative: {$price}");
        }

        if ($price > 10000.0) {
            throw new DomainException("Product price cannot exceed 10000: {$price}");
        }
    }

    #[Validate]
    public function validatePremium(#[Premium]
    float $price,): void
    {
        // Base validation first
        $this->validateProductPrice($price);

        if ($price < 100.0) {
            throw new DomainException("Premium price must be at least 100: {$price}");
        }
    }

    #[Validate]
    public function validateBudget(#[Budget]
    float $price,): void
    {
        // Base validation first
        $this->validateProductPrice($price);

        if ($price > 50.0) {
            throw new DomainException("Budget price must be at most 50: {$price}");
        }
    }

    #[Validate]
    public function validateProductPriceComparison(float $price, float $comparisonPrice): void
    {
        // Both prices must be valid
        $this->validateProductPrice($price);
        $this->validateProductPrice($comparisonPrice);

        if ($price === $comparisonPrice) {
            throw new DomainException('Product prices must be different for comparison');
        }
    }
}
