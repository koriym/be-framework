<?php

declare(strict_types=1);

namespace Be\Framework;

/**
 * Simple service for DI testing
 */
final class FakeService implements FakeServiceInterface
{
    public function __construct(
        public readonly string $name = 'TestService'
    ) {}
    
    public function getName(): string
    {
        return $this->name;
    }
}