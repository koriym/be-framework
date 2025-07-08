<?php

declare(strict_types=1);

namespace Ray\Compiler;

use Ray\Di\Container;
use Ray\Di\DependencyInterface;
use Ray\Di\NullDependency;
use Ray\Di\NullObjectDependency;

/**
 * @deprecated Compiler use BuiltInModule
 *
 * Convert NullObjectDependency to Dependency
 *
 * @psalm-import-type ScriptDir from CompileInjector
 */
final class CompileNullObject
{
    /** @param ScriptDir $scriptDir */
    public function __invoke(Container $container, string $scriptDir): void
    {
        $container->map(
            static function (DependencyInterface $dependency, string $string) use ($scriptDir): DependencyInterface {
                unset($string);
                if ($dependency instanceof NullObjectDependency) {
                    return $dependency->toNull($scriptDir);
                }

                return $dependency;
            }
        );
    }
}
