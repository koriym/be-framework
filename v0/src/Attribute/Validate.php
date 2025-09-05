<?php

declare(strict_types=1);

namespace Be\Framework\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_METHOD)]
final class Validate
{
}
