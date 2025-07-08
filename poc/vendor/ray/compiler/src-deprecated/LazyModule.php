<?php

declare(strict_types=1);

namespace Ray\Compiler;

use Ray\Di\AbstractModule;

/**
 * @deprecated Use Compiller and CompiledInjector instead
 *
 * Factory class for creating a lazy module
 *
 * Lazymodule is required to create CompileInjector. This utility class is useful when creating a module
 * from a variable that can be called for testing purposes.
 *
 * Please do not use this in production code. Instead of using this class, please create a class that implements LazyModuleInterface.
 */
final class LazyModule implements LazyModuleInterface
{
    /** @var AbstractModule  */
    private $module;

    public function __construct(AbstractModule $module)
    {
        $this->module = $module;
    }

    public function __invoke(): AbstractModule
    {
        return $this->module;
    }
}
