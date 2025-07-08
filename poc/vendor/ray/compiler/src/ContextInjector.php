<?php

declare(strict_types=1);

namespace Ray\Compiler;

use Ray\Di\AbstractModule;
use Ray\Di\InjectorInterface;

use function get_class;

/** @psalm-immutable */
final class ContextInjector
{
    public static function getInstance(AbstractInjectorContext $injectorContext): InjectorInterface
    {
        return CachedInjectorFactory::getInstance(
            get_class($injectorContext),
            $injectorContext->tmpDir,
            $injectorContext,
            $injectorContext->getCache(),
            $injectorContext->getSavedSingleton()
        );
    }

    public static function getOverrideInstance(
        AbstractInjectorContext $injectorContext,
        AbstractModule $overrideModule
    ): InjectorInterface {
        return CachedInjectorFactory::getOverrideInstance(
            $injectorContext->tmpDir,
            $injectorContext,
            $overrideModule,
            $injectorContext->getSavedSingleton()
        );
    }
}
