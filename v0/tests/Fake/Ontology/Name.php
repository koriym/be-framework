<?php

declare(strict_types=1);

namespace Be\Framework\Tests\Fake\Ontology;

use Be\Framework\Attribute\Validate;
use DomainException;

/**
 * Test semantic validator for Name
 */
final class Name
{
    #[Validate]
    public function validateName(string $name): void
    {
        if (trim($name) === '') {
            throw new DomainException('Name cannot be empty');
        }
    }
}