<?php

declare(strict_types=1);

namespace Be\Framework\Attribute;

use Attribute;

/**
 * A fake attribute class that is NOT marked with SemanticTag
 * Should be ignored during hierarchical semantic validation
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
final class FakeTag
{
    public function __construct(
        public readonly string $value = 'fake'
    ) {
    }
}