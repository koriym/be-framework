<?php

declare(strict_types=1);

namespace Ray\Compiler\Exception;

use RuntimeException;

class ScriptDirNotReadable extends RuntimeException implements ExceptionInterface
{
}
