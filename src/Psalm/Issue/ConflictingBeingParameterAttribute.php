<?php

declare(strict_types=1);

namespace Be\Framework\Psalm\Issue;

use Psalm\Issue\CodeIssue;

/**
 * Reported when a Being class constructor parameter has both #[Input] and #[Inject]
 *
 * The runtime rejects this ambiguity via
 * {@see \Be\Framework\Exception\ConflictingParameterAttributes}.
 */
final class ConflictingBeingParameterAttribute extends CodeIssue
{
    public const int ERROR_LEVEL = 1;

    public const int SHORTCODE = 9003;
}
