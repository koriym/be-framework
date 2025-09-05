<?php

declare(strict_types=1);

namespace Be\Framework\Fake\MyVendor\MyApp\SemanticTag;

use Attribute;
use Be\Framework\Attribute\SemanticTag;

/**
 * Teen semantic tag for age validation (13-19 years)
 */
#[SemanticTag(description: 'Age constraint for teenagers (13-19 years)')]
#[Attribute(Attribute::TARGET_PARAMETER)]
final class Teen
{
}