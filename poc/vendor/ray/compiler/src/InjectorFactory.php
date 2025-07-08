<?php

declare(strict_types=1);

namespace Ray\Compiler;

use Ray\Compiler\Annotation\Compile;
use Ray\Di\AbstractModule;
use Ray\Di\Annotation\ScriptDir;
use Ray\Di\Exception\Unbound;
use Ray\Di\Injector as RayInjector;
use Ray\Di\InjectorInterface;

use function is_dir;
use function mkdir;

/**
 * @psalm-immutable
 * @psalm-import-type ScriptDir from Types
 * @psalm-suppress DeprecatedClass
 */
final class InjectorFactory
{
    /**
     * @param callable(): AbstractModule $modules
     * @param ScriptDir                  $scriptDir
     */
    public static function getInstance(callable $modules, string $scriptDir): InjectorInterface
    {
        ! is_dir($scriptDir) && ! @mkdir($scriptDir) && ! is_dir($scriptDir);
        $module = $modules();
        $rayInjector = new RayInjector($module, $scriptDir);
        $isProd = false;
        try {
            /** @var bool $isProd */
            $isProd = $rayInjector->getInstance('', Compile::class);
        } catch (Unbound $e) {
        }

        if ($isProd === false) {
            return $rayInjector;
        }

        if ($modules instanceof LazyModuleInterface) {
            return self::getCompiledInjector($scriptDir, ($modules)());
        }

        return self::getCompiledInjector($scriptDir, $module);
    }

    /** @param ScriptDir $scriptDir */
    private static function getCompiledInjector(string $scriptDir, AbstractModule $module): InjectorInterface
    {
        (new Compiler())->compile($module, $scriptDir);

        return new CompiledInjector($scriptDir);
    }
}
