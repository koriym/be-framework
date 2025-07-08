<?php

declare(strict_types=1);

namespace Ray\Compiler;

use Ray\Di\AbstractModule;

class OverrideLazyModule implements LazyModuleInterface
{
    /** @var callable(): AbstractModule */
    private $modules;

    /** @var AbstractModule */
    private $overrideModule;

    /** @param callable(): AbstractModule $modules */
    public function __construct(callable $modules, AbstractModule $overrideModule)
    {
        $this->modules = $modules;
        $this->overrideModule = $overrideModule;
    }

    public function __invoke(): AbstractModule
    {
        $module = ($this->modules)();
        $module->override($this->overrideModule);

        return $module;
    }
}
