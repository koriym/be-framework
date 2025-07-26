<?php

declare(strict_types=1);

namespace Be\Framework;

/**
 * Debug service for Named testing
 */
final class FakeDebugService implements FakeServiceInterface
{
    public function __construct(
        public readonly string $name = 'DebugService'
    ) {}
    
    public function getName(): string
    {
        return $this->name;
    }
}