<?php

declare(strict_types=1);

namespace Be\Example\Ontology;

use Be\Framework\Attribute\Validate;

/**
 * Identifier
 *
 * @see https://schema.org/identifier
 */
final class Id
{
    #[Validate]
    public function validate(int|string $id): void
    {
    }
}
