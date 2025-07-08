<?php

declare(strict_types=1);

namespace Ray\Compiler;

use PhpParser\Node;
use PhpParser\PrettyPrinter\Standard;

/** @deprecated Use CompileInjector instead */
final class Code
{
    /** @var bool */
    public $isSingleton;

    /** @var IpQualifier|null */
    public $qualifiers;

    /** @var Node */
    private $node;

    /** @SuppressWarnings(PHPMD.BooleanArgumentFlag) // @phpstan-ignore-line */
    public function __construct(Node $node, bool $isSingleton = false, ?IpQualifier $qualifier = null)
    {
        $this->node = $node;
        $this->isSingleton = $isSingleton;
        $this->qualifiers = $qualifier;
    }

    public function __toString(): string
    {
        $prettyPrinter = new Standard();

        return $prettyPrinter->prettyPrintFile([$this->node]);
    }
}
