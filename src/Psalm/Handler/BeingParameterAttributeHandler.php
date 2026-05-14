<?php

declare(strict_types=1);

namespace Be\Framework\Psalm\Handler;

use Be\Framework\Psalm\Internal\AttributeNodeUtil;
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
 * Detects Being constructor parameters missing #[Input] / #[Inject] attributes
 *
 * Heuristic: a class is treated as a Being class if its constructor declares at
 * least one parameter annotated with #[Ray\InputQuery\Attribute\Input]. For such
 * classes, every constructor parameter must have either #[Input] or
 * #[Ray\Di\Di\Inject], otherwise {@see \Be\Framework\Exception\MissingParameterAttribute}
 * will be thrown at runtime by BecomingArguments.
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

        foreach ($ctor->params as $param) {
            self::checkParam($param, $className, $source, $aliases);
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

    private static function checkParam(Node\Param $param, string $className, FileSource $source, Aliases $aliases): void
    {
        if (AttributeNodeUtil::hasAttribute($param->attrGroups, self::INPUT_FQCN, $aliases)) {
            return;
        }

        if (AttributeNodeUtil::hasAttribute($param->attrGroups, self::INJECT_FQCN, $aliases)) {
            return;
        }

        $paramName = $param->var instanceof Node\Expr\Variable && is_string($param->var->name)
            ? $param->var->name
            : 'unknown';

        IssueBuffer::maybeAdd(
            new MissingBeingParameterAttribute(
                sprintf(
                    'Constructor parameter $%s of Being class %s is missing #[Input] or #[Inject] attribute',
                    $paramName,
                    $className,
                ),
                new CodeLocation($source, $param),
            ),
        );
    }
}
