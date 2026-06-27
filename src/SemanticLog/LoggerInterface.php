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
 * @psalm-import-type ConstructorArguments from Types
 * @psalm-import-type LogContextId from Types
 */
interface LoggerInterface
{
    /**
     * Log the start of a whole metamorphosis chain
     *
     * Opens an outer span that wraps every subsequent transformation so the
     * resulting log tree has a single root with sibling children, rather than
     * a forest of disconnected operations.
     *
     * @param object $input Initial input being that starts the chain
     *
     * @return string Open ID for correlating with closeChain
     */
    public function openChain(object $input): string;

    /**
     * Log the end of a whole metamorphosis chain
     *
     * @param object|null    $final     Terminal being reached on success; null if the chain failed
     * @param string         $openId    Open ID from the corresponding openChain call
     * @param Throwable|null $exception Exception that ended the chain, or null on success
     * @param string|null    $origin    Provenance of a semantic validation failure ('input'|'runtime'),
     *                                  or null when the failure is not a semantic validation error
     */
    public function closeChain(object|null $final, string $openId, Throwable|null $exception = null, string|null $origin = null): void;

    /**
     * Log transformation start
     *
     * @param object               $current  Current object being transformed
     * @param QualifiedClassName   $becoming Target class for transformation
     * @param ConstructorArguments $args     Pre-resolved constructor arguments for the target class
     * @phpstan-param class-string $becoming
     * @phpstan-param array<string, mixed> $args
     *
     * @return string Open ID for correlating with close
     */
    public function open(object $current, string $becoming, array $args): string;

    /**
     * Log transformation completion
     *
     * @param object|null    $result    Resulting object on success; null when the transformation failed
     * @param string         $openId    Open ID from corresponding open call
     * @param Throwable|null $exception Exception thrown when the transformation failed
     */
    public function close(object|null $result, string $openId, Throwable|null $exception = null): void;
}
