<?php

declare(strict_types=1);

namespace Be\Framework\SemanticLog;

/**
 * Interface for logging Be Framework transformations
 *
 * Simple open/close pattern for transformation logging.
 * 
 * @psalm-import-type BecomingClasses from \Be\Framework\Types
 * @psalm-import-type LogContextId from \Be\Framework\Types
 */
interface LoggerInterface
{
    /**
     * Log transformation start
     *
     * @param object               $current  Current object being transformed
     * @param string|array<string> $becoming Target class(es) for transformation
     *
     * @return string Open ID for correlating with close
     */
    public function open(object $current, string|array $becoming): string;

    /**
     * Log transformation completion
     *
     * @param object|null $result Resulting object (null if failed)
     * @param string      $openId Open ID from corresponding open call
     * @param string|null $error  Error message if transformation failed
     */
    public function close(object|null $result, string $openId, string|null $error = null): void;
}
