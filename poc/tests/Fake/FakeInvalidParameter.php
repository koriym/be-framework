<?php

declare(strict_types=1);

namespace Be\Framework;

/**
 * Class with parameter that has no attributes - should trigger validation error
 */
final class FakeInvalidParameter
{
    public function __construct(
        public readonly string $invalidParam  // No #[Input] or #[Inject] - should fail
    ) {}
}