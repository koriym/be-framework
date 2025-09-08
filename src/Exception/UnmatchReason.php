<?php

declare(strict_types=1);

namespace Be\Framework\Exception;

/**
 * Enumeration of possible reasons why a candidate class unmatched during type matching
 */
enum UnmatchReason: string
{
    case TypeMismatch = 'type_mismatch';
    case Constructor = 'constructor';
    case Validation = 'validation';
}
