<?php

declare(strict_types=1);

namespace Be\Framework\Tests\Fake\Ontology;

use Be\Framework\Attribute\Validate;
use DomainException;

/**
 * Test semantic validator for Age
 */
final class Age
{
    #[Validate]
    public function validateAge(int $age): void
    {
        if ($age < 0) {
            throw new DomainException('Age cannot be negative');
        }
        
        if ($age > 150) {
            throw new DomainException('Age cannot exceed 150');
        }
    }
}