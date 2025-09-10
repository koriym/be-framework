<?php

declare(strict_types=1);

namespace Be\Framework\Exception;

use InvalidArgumentException;
use ReflectionParameter;

use function sprintf;

/**
 * Thrown when scalar #[Inject] parameter lacks required #[Named] attribute
 *
 * Ray.Di historical compatibility requires scalar types to have #[Named] or default values.
 */
final class ScalarParameterRequiresNamed extends InvalidArgumentException
{
    public static function create(ReflectionParameter $param): self
    {
        return new self(sprintf(
            'Scalar parameter "%s" requires #[Named] attribute or default value',
            $param->getName(),
        ));
    }
}
