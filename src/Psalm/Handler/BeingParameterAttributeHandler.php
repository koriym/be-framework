<?php

declare(strict_types=1);

namespace Be\Framework\Psalm\Handler;

use Be\Framework\Psalm\Internal\AttributeNodeUtil;
use Be\Framework\Psalm\Issue\ConflictingBeingParameterAttribute;
use Be\Framework\Psalm\Issue\MissingBeingParameterAttribute;
use PhpParser\Node;
use Psalm\Aliases;
use Psalm\CodeLocation;
use Psalm\FileSource;
use Psalm\IssueBuffer;
use Psalm\Plugin\EventHandler\AfterClassLikeVisitInterface;
use Psalm\Plugin\EventHandler\Event\AfterClassLikeVisitEvent;

use function is_string;
use function sprintf;

/**
 * Detects invalid Being constructor parameter attributes
 *
 * Heuristic: a class is treated as a Being class if its constructor declares at
 * least one parameter annotated with #[Ray\InputQuery\Attribute\Input]. For such
 * classes, every constructor parameter must have exactly one of #[Input] or
 * #[Ray\Di\Di\Inject], otherwise BecomingArguments will throw at runtime.
 *
 * Skipped:
 *  - interfaces, traits, enums
 *  - abstract classes (cannot be instantiated as #[Be] target)
 *  - classes whose constructor has no #[Input] params (not a Being)
 */
final class BeingParameterAttributeHandler implements AfterClassLikeVisitInterface
{
    private const string INPUT_FQCN = 'Ray\\InputQuery\\Attribute\\Input';
    private const string INJECT_FQCN = 'Ray\\Di\\Di\\Inject';

    public static function afterClassLikeVisit(AfterClassLikeVisitEvent $event): void
    {
        $ctor = self::resolveBeingConstructor($event);

        if ($ctor === null) {
            return;
        }

        $source = $event->getStatementsSource();
        $aliases = $source->getAliases();
        $className = (string) $event->getStorage()->name;

        $suppressedIssues = $event->getStorage()->suppressed_issues;

        foreach ($ctor->params as $param) {
            self::checkParam($param, $className, $source, $aliases, $suppressedIssues);
        }
    }

    /**
     * Returns the constructor of a Being class, or null when this class isn't one
     */
    private static function resolveBeingConstructor(AfterClassLikeVisitEvent $event): Node\Stmt\ClassMethod|null
    {
        $class = $event->getStmt();

        if (! $class instanceof Node\Stmt\Class_ || $class->isAbstract()) {
            return null;
        }

        $ctor = AttributeNodeUtil::findConstructor($class);

        if ($ctor === null || $ctor->params === []) {
            return null;
        }

        $aliases = $event->getStatementsSource()->getAliases();

        if (! self::hasAnyInputParameter($ctor, $aliases)) {
            return null;
        }

        return $ctor;
    }

    private static function hasAnyInputParameter(Node\Stmt\ClassMethod $ctor, Aliases $aliases): bool
    {
        foreach ($ctor->params as $param) {
            if (AttributeNodeUtil::hasAttribute($param->attrGroups, self::INPUT_FQCN, $aliases)) {
                return true;
            }
        }

        return false;
    }

    /** @param array<array-key, string> $suppressedIssues */
    private static function checkParam(
        Node\Param $param,
        string $className,
        FileSource $source,
        Aliases $aliases,
        array $suppressedIssues,
    ): void {
        $hasInput = AttributeNodeUtil::hasAttribute($param->attrGroups, self::INPUT_FQCN, $aliases);
        $hasInject = AttributeNodeUtil::hasAttribute($param->attrGroups, self::INJECT_FQCN, $aliases);
        $paramName = $param->var instanceof Node\Expr\Variable && is_string($param->var->name)
            ? $param->var->name
            : 'unknown';

        if ($hasInput && $hasInject) {
            IssueBuffer::maybeAdd(
                new ConflictingBeingParameterAttribute(
                    sprintf(
                        'Constructor parameter $%s of Being class %s has both #[Input] and #[Inject] attributes',
                        $paramName,
                        $className,
                    ),
                    new CodeLocation($source, $param),
                ),
                $suppressedIssues,
            );

            return;
        }

        if ($hasInput || $hasInject) {
            return;
        }

        IssueBuffer::maybeAdd(
            new MissingBeingParameterAttribute(
                sprintf(
                    'Constructor parameter $%s of Being class %s is missing #[Input] or #[Inject] attribute',
                    $paramName,
                    $className,
                ),
                new CodeLocation($source, $param),
            ),
            $suppressedIssues,
        );
    }
}
