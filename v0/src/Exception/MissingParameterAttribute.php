<?php

declare(strict_types=1);

namespace Be\Framework\Exception;

use InvalidArgumentException;
use ReflectionParameter;

use function sprintf;

/**
 * Thrown when parameter lacks required #[Input] or #[Inject] attribute
 *
 * Be Framework requires explicit dependency declaration for safety and clarity.
 */
final class MissingParameterAttribute extends InvalidArgumentException
{
    /** @psalm-mutation-free */
    public static function create(ReflectionParameter $param): self
    {
        return new self(sprintf(
            'Parameter "%s" in %s::%s must have either #[Input] or #[Inject] attribute. ' .
            'Be Framework requires explicit dependency declaration for safety and clarity. ' .
            'Use #[Input] if this should come from the previous object, ' .
            'or #[Inject] if this should come from the DI container.',
            $param->getName(),
            $param->getDeclaringClass()?->getName() ?? 'Unknown',
            $param->getDeclaringFunction()->getName(),
        ));
    }
}
