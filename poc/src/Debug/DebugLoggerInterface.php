<?php

declare(strict_types=1);

namespace Ray\Framework\Debug;

/**
 * Simple logging interface for debug purposes
 */
interface DebugLoggerInterface
{
    /**
     * Log debug information
     */
    public function debug(string $message, array $context = []): void;

    /**
     * Log variable dump
     */
    public function dump(string $label, mixed $value): void;
}