<?php

declare(strict_types=1);

namespace Be\Framework\Exception;

use DomainException;

/**
 * Thrown when a constructor declares "I am not this"
 *
 * This exception represents an inner self-recognition during the journey of
 * becoming, rather than an external rejection. When a constructor throws
 * UnbecomingException, it signals that the current transformation candidate
 * is not its true self, prompting the framework to continue the journey
 * to the next candidate.
 *
 * Unlike infrastructure errors (DB failures, network timeouts) which should
 * propagate immediately, UnbecomingException signals an expected condition
 * in type-based branching logic.
 */
final class UnbecomingException extends DomainException
{
}
