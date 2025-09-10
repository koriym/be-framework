<?php

declare(strict_types=1);

namespace Be\Example\Semantic;

use Be\Example\Exception\EmptyNameException;
use Be\Example\Exception\InvalidNameFormatException;
use Be\Example\Tag\English;
use Be\Framework\Attribute\Validate;

use function preg_match;
use function trim;

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
    public function validateEnglish(#[English]
    string $name,): void
    {
        // English-specific validation (ASCII only)
        if (! preg_match('/^[A-Za-z\s]+$/', $name)) {
            throw new InvalidNameFormatException($name);
        }
    }
}
