<?php

declare(strict_types=1);

namespace Be\Framework\Psalm\Internal;

use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use Psalm\Aliases;

use function ltrim;
use function strpos;
use function strtolower;
use function substr;

/** @internal */
final class AttributeNodeUtil
{
    /**
     * Resolve the FQCN of any Name node using either the resolvedName attribute (set
     * when Psalm has run NameResolver) or the file's use-aliases as a fallback
     */
    public static function resolveNameFqcn(Name $name, Aliases|null $aliases = null): string|null
    {
        $resolved = $name->getAttribute('resolvedName');

        if ($resolved instanceof Name) {
            return ltrim($resolved->toString(), '\\');
        }

        if ($name instanceof FullyQualified) {
            return ltrim($name->toString(), '\\');
        }

        if ($aliases === null) {
            return null;
        }

        $first = $name->getFirst();
        $rest = '';
        $full = $name->toString();
        $sep = strpos($full, '\\');
        if ($sep !== false) {
            $first = substr($full, 0, $sep);
            $rest = substr($full, $sep);
        }

        $key = strtolower($first);
        if (isset($aliases->uses[$key])) {
            return ltrim($aliases->uses[$key] . $rest, '\\');
        }

        if ($aliases->namespace !== null && $aliases->namespace !== '') {
            return $aliases->namespace . '\\' . $full;
        }

        return $full;
    }

    /**
     * Resolve the FQCN of an attribute node, falling back to use-aliases when the
     * NameResolver hasn't tagged the node
     */
    public static function resolveAttributeFqcn(Attribute $attr, Aliases|null $aliases = null): string|null
    {
        return self::resolveNameFqcn($attr->name, $aliases);
    }

    /**
     * Check if an attribute group list contains an attribute with the given FQCN
     *
     * @param array<AttributeGroup> $attrGroups
     */
    public static function hasAttribute(array $attrGroups, string $targetFqcn, Aliases|null $aliases = null): bool
    {
        foreach ($attrGroups as $group) {
            foreach ($group->attrs as $attr) {
                if (self::resolveAttributeFqcn($attr, $aliases) === $targetFqcn) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Find the constructor ClassMethod of a Class_ AST node, or null if absent
     */
    public static function findConstructor(Node\Stmt\Class_ $class): Node\Stmt\ClassMethod|null
    {
        foreach ($class->stmts as $stmt) {
            if ($stmt instanceof Node\Stmt\ClassMethod && $stmt->name->toLowerString() === '__construct') {
                return $stmt;
            }
        }

        return null;
    }
}
