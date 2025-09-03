<?php

declare(strict_types=1);

namespace Be\Framework;

/**
 * Represents the absence of validation errors
 * 
 * Used to indicate successful validation in a type-safe manner.
 */
final class NullErrors extends Errors
{
    public function __construct()
    {
        parent::__construct([]);
    }
}
