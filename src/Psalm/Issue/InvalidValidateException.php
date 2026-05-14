<?php

declare(strict_types=1);

namespace Be\Framework\Psalm\Issue;

use Psalm\Issue\CodeIssue;

/**
 * Reported when a #[Validate] method throws an exception that does not extend DomainException
 *
 * The framework's {@see \Be\Framework\SemanticVariable\SemanticValidator} only
 * catches DomainException; any other exception propagates as an uncaught error,
 * which silently bypasses validation.
 */
final class InvalidValidateException extends CodeIssue
{
    public const int ERROR_LEVEL = 1;

    public const int SHORTCODE = 9002;
}
