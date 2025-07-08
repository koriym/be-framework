<?php

declare(strict_types=1);

namespace Ray\Compiler;

use Ray\Compiler\Exception\CompileLockFailed;
use Ray\Di\AbstractModule;
use Ray\Di\AcceptInterface;
use Ray\Di\Annotation\ScriptDir;
use Ray\Di\ContainerFactory;
use Ray\Di\DependencyInterface;

use function assert;
use function fclose;
use function flock;
use function fopen;
use function is_string;

use const LOCK_EX;
use const LOCK_UN;

/**
 *  Module Compiler
 *
 *  Compiles module bindings into PHP files for CompiledInjector.
 *  The compilation process includes:
 *  - Acquiring a file lock to ensure thread safety
 *  - Converting dependencies into PHP scripts using CompileVisitor
 *  - Saving compiled scripts to the target directory
 *
 * @psalm-import-type ScriptDir from Types
 */
final class Compiler
{
    /**
     * Compiles a given module into Scripts
     *
     * @param ScriptDir $scriptDir
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter) // @phpstan-ignore-line
     */
    public function compile(AbstractModule $module, string $scriptDir): Scripts
    {
        $module->install(new CompilerModule($scriptDir));

        // Lock
        $fp = fopen($scriptDir . '/compile.lock', 'a+');
        if ($fp === false || ! flock($fp, LOCK_EX)) {
            // @codeCoverageIgnoreStart
            if ($fp !== false) {
                fclose($fp);
            }

            throw new CompileLockFailed($scriptDir);
            // @codeCoverageIgnoreEnd
        }

        $scripts = new Scripts();
        $container = (new ContainerFactory())($module, $scriptDir);
        // Compile dependencies
        $compileVisitor = new CompileVisitor($container);
        $container->map(static function (DependencyInterface $dependency, string $key) use ($scripts, $compileVisitor): DependencyInterface {
            assert($dependency instanceof AcceptInterface);
            $script = $dependency->accept($compileVisitor);
            assert(is_string($script));
            $scripts->add($key, $script);

            return $dependency;
        });
        $scripts->save($scriptDir);
        // Unlock
        flock($fp, LOCK_UN);
        fclose($fp);

        return $scripts;
    }
}
