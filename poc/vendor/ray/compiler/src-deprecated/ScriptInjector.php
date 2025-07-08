<?php

declare(strict_types=1);

namespace Ray\Compiler;

use Ray\Compiler\Exception\Unbound;
use Ray\Di\AbstractModule;
use Ray\Di\Annotation\ScriptDir;
use Ray\Di\AssistedModule;
use Ray\Di\Bind;
use Ray\Di\Dependency;
use Ray\Di\DependencyInterface;
use Ray\Di\Name;
use Ray\Di\NullModule;
use Ray\Di\ProviderSetModule;
use ReflectionParameter;
use function assert;
use function count;
use function error_log;
use function error_reporting;
use function file_exists;
use function file_get_contents;
use function glob;
use function in_array;
use function is_bool;
use function is_callable;
use function is_dir;
use function rmdir;
use function rtrim;
use function serialize;
use function spl_autoload_register;
use function sprintf;
use function str_replace;
use function unlink;
use function unserialize;
use const DIRECTORY_SEPARATOR;
use const E_NOTICE;

/**
 * @psalm-import-type ScriptDir from CompileInjector
 * @psalm-import-type Ip from CompileInjector
 * @deprecated Use CompileInjector instead
 */
final class ScriptInjector implements ScriptInjectorInterface
{
    public const MODULE = '/_module.txt';

    public const AOP = '/_aop.txt';

    public const INSTANCE = '%s/%s.php';

    public const QUALIFIER = '%s/qualifier/%s-%s-%s';

    /** @var non-empty-string */
    private $scriptDir;

    /**
     * Injection Point
     *
     * [$class, $method, $parameter]
     *
     * @var Ip
     */
    private $ip = ['', '', ''];

    /**
     * Singleton instance container
     *
     * @var array<object>
     */
    private $singletons = [];

    /** @var array<callable> */
    private $functions;

    /** @var callable */
    private $lazyModule;

    /** @var AbstractModule|null */
    private $module;

    /** @var ?array<DependencyInterface> */
    private $container;

    /** @var bool */
    private $isModuleLocked = false;

    /** @var array<string> */
    private static $scriptDirs = [];

    /**
     * @param ScriptDir $scriptDir  generated instance script folder path
     * @param callable  $lazyModule callable variable which return AbstractModule instance
     *
     * @psalm-suppress UnresolvableInclude
     */
    public function __construct($scriptDir, ?callable $lazyModule = null)
    {
        $this->init($scriptDir, $lazyModule);
    }

    /** @param ScriptDir $scriptDir */
    private function init(string $scriptDir, ?callable $lazyModule): void
    {
        $this->scriptDir = $scriptDir;
        $this->lazyModule = is_callable($lazyModule) ? $lazyModule : static /** @return NullModule */function (): NullModule {
            return new NullModule();
        };
        $this->registerLoader();
        $prototype =
            /**
             * @param Ip $injectionPoint
             *
             * @return mixed
             */
            function (string $dependencyIndex, array $injectionPoint = ['', '', '']) {
                $this->ip = $injectionPoint; // @phpstan-ignore-line
                [$prototype, $singleton, $injectionPoint, $injector] = $this->functions;

                $instanceFile = $this->getInstanceFile($dependencyIndex);
                assert(file_exists($instanceFile), "File not found: {$instanceFile}");

                return require $instanceFile;
            };
        $singleton =
            /**
             * @param Ip $injectionPoint
             *
             * @return mixed
             */
            function (string $dependencyIndex, $injectionPoint = ['', '', '']) {
                if (isset($this->singletons[$dependencyIndex])) {
                    return $this->singletons[$dependencyIndex];
                }

                $this->ip = $injectionPoint;
                [$prototype, $singleton, $injectionPoint, $injector] = $this->functions;
                $instanceFile = $this->getInstanceFile($dependencyIndex);
                assert(file_exists($instanceFile), "File not found: {$instanceFile}");

                $instance = require $instanceFile;
                $this->singletons[$dependencyIndex] = $instance;

                return $instance;
            };
        $injectionPoint = function () use ($scriptDir): InjectionPoint {
            return new InjectionPoint(new ReflectionParameter([$this->ip[0], $this->ip[1]], $this->ip[2]));
        };
        $injector = function (): self {
            return $this;
        };
        $this->functions = [$prototype, $singleton, $injectionPoint, $injector];
    }

    /** @return list<string> */
    public function __sleep()
    {
        $this->saveModule();

        return ['scriptDir', 'singletons'];
    }

    public function __wakeup()
    {
        $this->init($this->scriptDir, function () {
            return $this->getModule();
        });
    }

    /**
     * {@inheritDoc}
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable) // @phpstan-ignore-line
     */
    public function getInstance($interface, $name = Name::ANY)
    {
        $dependencyIndex = $interface . '-' . $name;
        if (isset($this->singletons[$dependencyIndex])) {
            return $this->singletons[$dependencyIndex];
        }

        [$prototype, $singleton, $injectionPoint, $injector] = $this->functions;
        /** @psalm-suppress RedundantConditionGivenDocblockType */ // assert for serialization
        assert(is_callable($prototype), 'prototype is not callable'); // @phpstan-ignore-line
        /** @psalm-suppress UnresolvableInclude */
        $instance = require $this->getInstanceFile($dependencyIndex);
        /** @psalm-suppress UndefinedVariable */
        $isSingleton = isset($isSingleton) && $isSingleton;
        if ($isSingleton) {
            $this->singletons[$dependencyIndex] = $instance;
        }

        /**
         * @psalm-var T $instance
         * @phpstan-var mixed $instance
         */
        return $instance;
    }

    public function clear(): void
    {
        $unlink = static function (string $path) use (&$unlink): void {
            foreach ((array) glob(rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*') as $f) {
                $file = (string) $f;
                is_dir($file) ? $unlink($file) : unlink($file);
                @rmdir($file);
            }
        };
        $unlink($this->scriptDir);
    }

    public function isSingleton(string $dependencyIndex): bool
    {
        if ($this->container === null) {
            $module = $this->getModule();
            /** @var AbstractModule $module */
            $this->container = $module->getContainer()->getContainer();
        }

        if (! isset($this->container[$dependencyIndex])) {
            throw new Unbound($dependencyIndex);
        }

        $dependency = $this->container[$dependencyIndex];

        return $dependency instanceof Dependency ? (bool) (new PrivateProperty())($dependency, 'isSingleton') : false;
    }

    private function getModule(): AbstractModule
    {
        $modulePath = $this->scriptDir . self::MODULE;
        if (! file_exists($modulePath)) {
            // @codeCoverageIgnoreStart
            error_log(sprintf('ModuleFileNotFound: %s', $modulePath));
            error_log('Please report the issue at https://github.com/ray-di/Ray.Compiler/issues');

            return new NullModule();
            // @codeCoverageIgnoreEnd
        }

        $serialized = file_get_contents($modulePath);
        assert(! is_bool($serialized));
        $er = error_reporting(error_reporting() ^ E_NOTICE);
        $module = unserialize($serialized, ['allowed_classes' => true]);
        error_reporting($er);
        assert($module instanceof AbstractModule);

        return $module;
    }

    /**
     * Return compiled script file name
     */
    private function getInstanceFile(string $dependencyIndex): string
    {
        $file = sprintf(self::INSTANCE, $this->scriptDir, str_replace('\\', '_', $dependencyIndex));
        if (file_exists($file)) {
            return $file;
        }

        $this->compileOnDemand($dependencyIndex);
        assert(file_exists($file));

        return $file;
    }

    private function saveModule(): void
    {
        if ($this->isModuleLocked || file_exists($this->scriptDir . self::MODULE)) {
            return;
        }

        $this->isModuleLocked = true;
        $module = $this->module instanceof AbstractModule ? $this->module : $this->evaluateModule($this->lazyModule);
        (new FilePutContents())($this->scriptDir . self::MODULE, serialize($module));
    }

    private function registerLoader(): void
    {
        if (in_array($this->scriptDir, self::$scriptDirs, true)) {
            return;
        }

        if (self::$scriptDirs === []) {
            spl_autoload_register(
                static function (string $class): void {
                    foreach (self::$scriptDirs as $scriptDir) {
                        $file = sprintf('%s/%s.php', $scriptDir, str_replace('\\', '_', $class));
                        if (file_exists($file)) {
                            require $file; // @codeCoverageIgnore
                        }
                    }
                }
            );
        }

        self::$scriptDirs[] = $this->scriptDir;
    }

    private function compileOnDemand(string $dependencyIndex): void
    {
        if (! $this->module instanceof AbstractModule) {
            $this->module = $this->evaluateModule($this->lazyModule);
        }

        $isFirstCompile = ! file_exists($this->scriptDir . self::AOP);
        if ($isFirstCompile) {
            $this->firstCompile();
        }

        (new Bind($this->module->getContainer(), ''))->annotatedWith(ScriptDir::class)->toInstance($this->scriptDir); // @phpstan-ignore-line
        (new OnDemandCompiler($this, $this->scriptDir, $this->module))($dependencyIndex);  // @phpstan-ignore-line
    }

    private function firstCompile(): void
    {
        assert($this->module instanceof AbstractModule);
        $compiler = new DiCompiler($this->module, $this->scriptDir);
        $compiler->savePointcuts($this->module->getContainer());
        $this->saveModule();
        $compiler->compile();
    }

    private function evaluateModule(callable $lazyModule): AbstractModule
    {
        $module = ($lazyModule)();
        assert($module instanceof AbstractModule);
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
