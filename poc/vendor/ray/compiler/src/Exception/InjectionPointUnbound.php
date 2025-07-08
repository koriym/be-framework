<?php

declare(strict_types=1);

namespace Ray\Compiler\Exception;

/**
 * Represents an unbound injection point.
 *
 * This method is thrown if the injection point is not bound.
 * For example, when retrieving the root object.
 */
class InjectionPointUnbound extends Unbound
{
}
