<?php

declare(strict_types=1);

namespace Be\Framework;

/**
 * Interface for testing Named with object types
 */
interface FakeServiceInterface
{
    public function getName(): string;
}