<?php

declare(strict_types=1);

namespace Ray\Compiler;

use Ray\Di\AbstractModule;
use Ray\Di\AssistedModule;
use Ray\Di\ProviderSetModule;
use function count;

/** @deprecated Use CompilerModule */
final class InstallBuiltinModule
{
    public function __invoke(AbstractModule $module): AbstractModule
    {
        $module = new DiCompileModule(true, $module);
        $module->install(new AssistedModule());
        $module->install(new ProviderSetModule());
        $module->install(new PramReaderModule());
        $hasMultiBindings = count($module->getContainer()->multiBindings);
        if ($hasMultiBindings) {
            $module->install(new MapModule());
        }

        return $module;
    }
}
