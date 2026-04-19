<?php

declare(strict_types=1);

namespace Be\Framework\SemanticLog;

use Be\Framework\Types;
use Throwable;

/**
 * Interface for logging Be Framework transformations
 *
 * Simple open/close pattern for transformation logging.
 *
 * @psalm-import-type QualifiedClassName from Types
 * @psalm-import-type QualifiedClasses from Types
 * @psalm-import-type LogContextId from Types
 */
interface LoggerInterface
{
    /**
     * Log transformation start
     *
     * @param object                              $current  Current object being transformed
     * @param QualifiedClassName|QualifiedClasses $becoming Target class(es) for transformation
     * @phpstan-param string|array<string> $becoming
     *
     * @return string Open ID for correlating with close
     */
    public function open(object $current, string|array $becoming): string;

    /**
     * Log transformation completion
     *
     * @param object|null    $result    Resulting object on success; null when the transformation failed
     * @param string         $openId    Open ID from corresponding open call
     * @param Throwable|null $exception Exception thrown when the transformation failed
     */
    public function close(object|null $result, string $openId, Throwable|null $exception = null): void;
}
