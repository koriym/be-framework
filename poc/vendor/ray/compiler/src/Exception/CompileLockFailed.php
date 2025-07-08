<?php

declare(strict_types=1);

namespace Ray\Compiler\Exception;

use RuntimeException;

class CompileLockFailed extends RuntimeException implements ExceptionInterface
{
}
