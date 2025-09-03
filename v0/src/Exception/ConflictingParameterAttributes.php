<?php

declare(strict_types=1);

namespace Be\Framework\Exception;

use InvalidArgumentException;
use ReflectionParameter;

use function sprintf;

/**
 * Thrown when parameter has both #[Input] and #[Inject] attributes
 *
 * These attributes are mutually exclusive to ensure clear parameter semantics.
 */
final class ConflictingParameterAttributes extends InvalidArgumentException
{
    public static function create(ReflectionParameter $param): self
    {
        return new self(sprintf(
            'Parameter "%s" in %s::%s cannot have both #[Input] and #[Inject] attributes simultaneously. ' .
            'These attributes are mutually exclusive to ensure clear and unambiguous parameter semantics.',
            $param->getName(),
            $param->getDeclaringClass()->getName(),
            $param->getDeclaringFunction()->getName(),
        ));
    }
}
