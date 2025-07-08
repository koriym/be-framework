<?php

declare(strict_types=1);

namespace Ray\Framework\Debug;

use function var_dump;

/**
 * Echo-based debug logger (for backward compatibility)
 */
final class EchoDebugLogger implements DebugLoggerInterface
{
    public function debug(string $message, array $context = []): void
    {
        echo $message . "\n";
        if (! empty($context)) {
            var_dump($context);
        }
    }

    public function dump(string $label, mixed $value): void
    {
        echo $label . ': ';
        var_dump($value);
    }
}