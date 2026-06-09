<?php

declare(strict_types=1);

namespace Be\Framework\Exception;

/**
 * Thrown when semantic validation fails on the first metamorphosis (input error)
 *
 * The very first transformation turns the user-supplied input object into its
 * first form, validating the data that flowed in from the outside. A failure
 * here means the input itself is invalid - an error attributable to the caller.
 *
 * @see RuntimeSemanticVariableException for failures in later transformations
 */
final class InputSemanticVariableException extends SemanticVariableException
{
}
