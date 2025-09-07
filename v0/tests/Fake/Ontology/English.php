<?php

declare(strict_types=1);

namespace Be\Framework\Tests\Fake\Ontology;

use Be\Framework\Attribute\Validate;
use DomainException;

/**
 * Test semantic validator for English language constraints
 */
final class English
{
    #[Validate]
    public function validateEnglish(string $text): void
    {
        // Simple check for English characters (ASCII printable)
        if (!ctype_print($text) || preg_match('/[^\x20-\x7E]/', $text)) {
            throw new DomainException('Text must contain only English characters');
        }
        
        if (empty(trim($text))) {
            throw new DomainException('English text cannot be empty');
        }
    }
}