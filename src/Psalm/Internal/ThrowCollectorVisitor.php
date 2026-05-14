<?php

declare(strict_types=1);

namespace Be\Framework\Psalm\Internal;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

/**
 * AST visitor that collects throw expressions from a function-like body
 *
 * Stops at nested function-likes (closures, arrow functions, nested functions,
 * methods, anonymous classes) so that throws inside them are reported by their
 * own analysis pass rather than the enclosing #[Validate] method.
 *
 * @internal
 */
final class ThrowCollectorVisitor extends NodeVisitorAbstract
{
    /** @var list<Node\Expr\Throw_> */
    public array $throws = [];

    public function enterNode(Node $node): int|null
    {
        if (
            $node instanceof Node\Expr\Closure
            || $node instanceof Node\Expr\ArrowFunction
            || $node instanceof Node\Stmt\Function_
            || $node instanceof Node\Stmt\ClassMethod
            || $node instanceof Node\Stmt\Class_
        ) {
            return NodeTraverser::DONT_TRAVERSE_CHILDREN;
        }

        if ($node instanceof Node\Expr\Throw_) {
            $this->throws[] = $node;
        }

        return null;
    }
}
