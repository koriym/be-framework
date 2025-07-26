<?php

declare(strict_types=1);

namespace Be\Framework;

/**
 * Interface for resolving constructor arguments for metamorphosis transformations
 *
 * Resolves arguments needed for the destination class constructor from the current object,
 * following Be Framework's metamorphic programming paradigm.
 */
interface BecomingArgumentsInterface
{
    /**
     * Resolve constructor arguments for the becoming (destination) class
     *
     * @param object $current  The current object being transformed
     * @param string $becoming The fully qualified class name of the destination class
     *
     * @return array<mixed> Associative array of constructor arguments [paramName => value]
     */
    public function __invoke(object $current, string $becoming): array;
}
