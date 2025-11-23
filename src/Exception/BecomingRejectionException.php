<?php

declare(strict_types=1);

namespace Be\Framework\Exception;

use DomainException;

/**
 * Thrown when a constructor intentionally rejects a transformation during branching
 *
 * This exception allows constructors to explicitly indicate that they reject
 * the current transformation context, enabling the framework to try alternative
 * candidate classes. Unlike infrastructure errors (DB failures, network timeouts),
 * which should propagate immediately, BecomingRejectionException signals an expected
 * condition in type-based branching logic.
 */
final class BecomingRejectionException extends DomainException
{
}
