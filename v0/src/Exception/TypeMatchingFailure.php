<?php

declare(strict_types=1);

namespace Be\Framework\Exception;

use RuntimeException;

/**
 * Thrown when type matching fails during array-based becoming
 */
final class TypeMatchingFailure extends RuntimeException
{
}
