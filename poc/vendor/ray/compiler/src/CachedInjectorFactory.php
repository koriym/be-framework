<?php

declare(strict_types=1);

namespace Ray\Compiler;

use Doctrine\Common\Cache\CacheProvider;
use Ray\Di\AbstractModule;
use Ray\Di\Annotation\ScriptDir;
use Ray\Di\InjectorInterface;
use Ray\Di\NullCache;

use function assert;
use function serialize;
use function unserialize;

/** @psalm-import-type ScriptDir from Types */
final class CachedInjectorFactory
{
    /** @var array<string, string> */
    private static $injectors = [];

    /**
     * @param callable(): AbstractModule $modules
     * @param array<class-string>        $savedSingletons
     * @param ScriptDir                  $scriptDir
     */
    public static function getInstance(string $injectorId, string $scriptDir, callable $modules, ?CacheProvider $cache = null, array $savedSingletons = []): InjectorInterface
    {
        if (isset(self::$injectors[$injectorId])) {
            /** @noinspection UnserializeExploitsInspection */
            $injector = unserialize(self::$injectors[$injectorId]);
            assert($injector instanceof InjectorInterface);

            return $injector;
        }

        /** @psalm-suppress DeprecatedClass */
        $cache = $cache ?? new NullCache();
        $cache->setNamespace($injectorId);
        /** @var ScriptInjectorInterface|null $cachedInjector */
        $cachedInjector = $cache->fetch(ScriptInjectorInterface::class);
        if ($cachedInjector instanceof ScriptInjectorInterface) {
            return $cachedInjector; // @codeCoverageIgnore
        }

        $injector = self::getInjector($modules, $scriptDir, $savedSingletons);
        if ($injector instanceof ScriptInjectorInterface) {
            $cache->save(ScriptInjectorInterface::class, $injector);
        }

        self::$injectors[$injectorId] = serialize($injector);

        return $injector;
    }

    /**
     * @param callable(): AbstractModule $modules
     * @param array<class-string>        $savedSingletons
     *                                                   @param ScriptDir                  $scriptDir
     */
    public static function getOverrideInstance(
        string $scriptDir,
        callable $modules,
        AbstractModule $overrideModule,
        array $savedSingletons = []
    ): InjectorInterface {
        return self::getInjector($modules, $scriptDir, $savedSingletons, $overrideModule);
    }

    /**
     * @param callable(): AbstractModule $modules
     * @param array<class-string>        $savedSingletons
     * @param ScriptDir                  $scriptDir
     */
    private static function getInjector(callable $modules, string $scriptDir, array $savedSingletons, ?AbstractModule $module = null): InjectorInterface
    {
        if ($module !== null) {
            $modules = new OverrideLazyModule($modules, $module);
        }

        $injector = InjectorFactory::getInstance($modules, $scriptDir);
        foreach ($savedSingletons as $singleton) {
            $injector->getInstance($singleton);
        }

        return $injector;
    }
}
