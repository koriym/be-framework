<?php

declare(strict_types=1);

namespace Be\Example\Ontology;

use Be\Framework\Attribute\Validate;
use Be\Example\Exception\EmptyNameException;
use Be\Example\Exception\InvalidNameFormatException;
use Be\Example\Tag\English;

/**
 * Person name
 *
 * Validates that names are non-empty and contain only letters and spaces.
 *
 * @link https://schema.org/Person Person schema
 * @link https://schema.org/name name property
 * @see https://schema.org/givenName
 * @see https://schema.org/familyName
 */
final class Name
{
    #[Validate]
    public function validate(string $name): void
    {
        if (empty(trim($name))) {
            throw new EmptyNameException();
        }
    }

    #[Validate]
    public function validateEnglish(#[English] string $name): void
    {
        // English-specific validation (ASCII only)
        if (! preg_match('/^[A-Za-z\s]+$/', $name)) {
            throw new InvalidNameFormatException($name);
        }
    }
}
