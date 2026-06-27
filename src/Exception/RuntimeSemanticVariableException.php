<?php

declare(strict_types=1);

namespace Be\Framework\Exception;

/**
 * Thrown when semantic validation fails after the first metamorphosis (runtime error)
 *
 * Once data has passed input validation, any further metamorphosis works on
 * already-validated state. A validation failure at this stage signals an
 * internal inconsistency in the transformation logic rather than bad input -
 * an error attributable to the program itself.
 *
 * @see InputSemanticVariableException for failures in the first transformation
 */
final class RuntimeSemanticVariableException extends SemanticVariableException
{
}
