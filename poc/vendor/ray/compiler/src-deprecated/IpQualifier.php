<?php

declare(strict_types=1);

namespace Ray\Compiler;

use ReflectionParameter;
use function serialize;

/**
 * @deprecated since 1.11.0
 * Since CompileVisitor pattern is used, this class is no longer needed.
 */
final class IpQualifier
{
    /** @var ReflectionParameter */
    public $param;

    /** @var mixed */
    public $qualifier;

    public function __construct(ReflectionParameter $param, object $qualifier)
    {
        $this->param = $param;
        $this->qualifier = $qualifier;
    }

    public function __toString(): string
    {
        return serialize($this->qualifier); // @codeCoverageIgnore
    }
}
