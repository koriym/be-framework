<?php

declare(strict_types=1);

namespace Be\Framework\Psalm\Handler;

use Be\Framework\Psalm\Internal\AttributeNodeUtil;
use Be\Framework\Psalm\Internal\ThrowCollectorVisitor;
use Be\Framework\Psalm\Issue\InvalidValidateException;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use Psalm\Aliases;
use Psalm\Codebase;
use Psalm\CodeLocation;
use Psalm\IssueBuffer;
use Psalm\NodeTypeProvider;
use Psalm\Plugin\EventHandler\AfterFunctionLikeAnalysisInterface;
use Psalm\Plugin\EventHandler\Event\AfterFunctionLikeAnalysisEvent;
use Psalm\StatementsSource;
use Psalm\Type\Atomic\TNamedObject;

use function ltrim;
use function sprintf;

/**
 * Detects #[Validate] methods that throw exceptions not extending DomainException
 *
 * The framework's SemanticValidator only catches DomainException; throws of any
 * other type silently propagate, bypassing validation entirely. This handler
 * walks the body of #[Validate]-annotated methods, collects throw statements,
 * resolves their exception types via Psalm's NodeTypeProvider, and reports any
 * type that does not extend \DomainException.
 */
final class ValidateThrowHandler implements AfterFunctionLikeAnalysisInterface
{
    private const string VALIDATE_FQCN = 'Be\\Framework\\Attribute\\Validate';
    private const string DOMAIN_EXCEPTION = 'DomainException';

    public static function afterStatementAnalysis(AfterFunctionLikeAnalysisEvent $event): bool|null
    {
        $stmt = $event->getStmt();

        if (! $stmt instanceof Node\Stmt\ClassMethod) {
            return null;
        }

        $source = $event->getStatementsSource();
        $aliases = $source->getAliases();

        if (! AttributeNodeUtil::hasAttribute($stmt->attrGroups, self::VALIDATE_FQCN, $aliases)) {
            return null;
        }

        if ($stmt->stmts === null) {
            return null;
        }

        $visitor = new ThrowCollectorVisitor();
        $traverser = new NodeTraverser();
        $traverser->addVisitor($visitor);
        $traverser->traverse($stmt->stmts);

        if ($visitor->throws === []) {
            return null;
        }

        $codebase = $event->getCodebase();
        $ntp = $event->getNodeTypeProvider();
        $methodName = sprintf(
            '%s::%s',
            $source->getFQCLN() ?? 'unknown',
            $stmt->name->toString(),
        );

        foreach ($visitor->throws as $throw) {
            self::checkThrow($throw, $methodName, $source, $codebase, $ntp, $aliases);
        }

        return null;
    }

    private static function checkThrow(
        Node\Expr\Throw_ $throw,
        string $methodName,
        StatementsSource $source,
        Codebase $codebase,
        NodeTypeProvider $ntp,
        Aliases $aliases,
    ): void {
        $names = self::resolveThrownClassNames($throw->expr, $ntp, $aliases);

        if ($names === null) {
            return; // type unresolved — prefer false negative
        }

        foreach ($names as $name) {
            if (self::extendsDomainException($name, $codebase)) {
                continue;
            }

            IssueBuffer::maybeAdd(
                new InvalidValidateException(
                    sprintf(
                        '#[Validate] method %s throws %s, which does not extend \\DomainException; '
                        . 'the framework only catches DomainException, so this throw bypasses validation',
                        $methodName,
                        $name,
                    ),
                    new CodeLocation($source, $throw),
                ),
                $source->getSuppressedIssues(),
            );

            return; // one report per throw
        }
    }

    /** @return list<string>|null FQCNs of possible thrown classes, or null if unknown */
    private static function resolveThrownClassNames(Node\Expr $expr, NodeTypeProvider $ntp, Aliases $aliases): array|null
    {
        if ($expr instanceof Node\Expr\New_ && $expr->class instanceof Node\Name) {
            $fqcn = AttributeNodeUtil::resolveNameFqcn($expr->class, $aliases);

            return $fqcn !== null ? [$fqcn] : null;
        }

        $type = $ntp->getType($expr);

        if ($type === null) {
            return null;
        }

        $names = [];
        foreach ($type->getAtomicTypes() as $atomic) {
            if (! $atomic instanceof TNamedObject) {
                return null; // mixed / non-object atomic — give up
            }

            $names[] = ltrim($atomic->value, '\\');
        }

        return $names;
    }

    private static function extendsDomainException(string $fqcn, Codebase $codebase): bool
    {
        if ($fqcn === self::DOMAIN_EXCEPTION) {
            return true;
        }

        if (! $codebase->classOrInterfaceExists($fqcn)) {
            return false;
        }

        return $codebase->classExtends($fqcn, self::DOMAIN_EXCEPTION);
    }
}
