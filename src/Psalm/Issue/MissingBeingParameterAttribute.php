<?php

declare(strict_types=1);

namespace Be\Framework\Psalm\Issue;

use Psalm\Issue\CodeIssue;

/**
 * Reported when a Being class constructor parameter lacks both #[Input] and #[Inject]
 *
 * A "Being class" is identified heuristically by having at least one constructor
 * parameter annotated with #[Ray\InputQuery\Attribute\Input]. Once identified,
 * every other constructor parameter must also have either #[Input] or #[Inject];
 * otherwise the framework throws {@see \Be\Framework\Exception\MissingParameterAttribute}
 * at runtime.
 */
final class MissingBeingParameterAttribute extends CodeIssue
{
    public const int ERROR_LEVEL = 1;

    public const int SHORTCODE = 9001;
}
