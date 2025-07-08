<?php

declare(strict_types=1);

namespace Ray\Compiler;

use Ray\Aop\Compiler;
use Ray\Di\AbstractModule;
use Ray\Di\Annotation\ScriptDir;
use Ray\Di\Bind;
use Ray\Di\Container;
use Ray\Di\Exception\Unbound;
use Ray\Di\InjectorInterface;
use Ray\Di\Name;
use ReflectionProperty;
use function assert;
use function fclose;
use function fopen;
use function fwrite;
use function is_resource;
use function is_string;
use function ksort;
use function serialize;
use function sprintf;
use function sys_get_temp_dir;

/**
 * @deprecated Use CimpileInjector instead
 * @psalm-import-type ScriptDir from CompileInjector
 */
final class DiCompiler implements InjectorInterface
{
    /** @var ScriptDir */
    private $scriptDir;

    /** @var Container */
    private $container;

    /** @var DependencyCode */
    private $dependencyCompiler;

    /** @var AbstractModule|null */
    private $module;

    /** @var DependencySaver */
    private $dependencySaver;

    /** @var FilePutContents */
    private $filePutContents;

    /** @param ScriptDir $scriptDir */
    public function __construct(AbstractModule $module, string $scriptDir)
    {
        /** @var ScriptDir $scriptDir */
        $scriptDir = $scriptDir ?: sys_get_temp_dir();
        $this->scriptDir = $scriptDir;
        $this->container = $module->getContainer();
        $this->dependencyCompiler = new DependencyCode($this->container);
        $this->module = $module;
        $this->dependencySaver = new DependencySaver($scriptDir);
        $this->filePutContents = new FilePutContents();
        (new CompileNullObject())($this->container, $this->scriptDir);

        // Weave AssistedInterceptor and bind InjectorInterface for self
        $module->getContainer()->weaveAspects(new Compiler($scriptDir));
        (new Bind($this->container, InjectorInterface::class))->toInstance($this);
        (new Bind($this->container, ''))->annotatedWith(ScriptDir::class)->toInstance($scriptDir);
    }

    /**
     * {@inheritDoc}
     */
    public function getInstance($interface, $name = Name::ANY)
    {
        $this->compile();

        return (new ScriptInjector($this->scriptDir))->getInstance($interface, $name);
    }

    /**
     * Compile for ScriptInjector
     */
    public function compile(): void
    {
        $this->compileContainer();
        $this->savePointcuts($this->container);
        ($this->filePutContents)($this->scriptDir . ScriptInjector::MODULE, serialize($this->module));
    }

    /**
     * Compile for CompileInjector
     */
    public function compileContainer(): void
    {
        $scriptDir = $this->container->getInstance('', ScriptDir::class);
        $container = $this->container->getContainer();
        assert(is_string($scriptDir));
        $fp = fopen(sprintf('%s/_compile.log', $this->scriptDir), 'a');
        assert(is_resource($fp));
        ksort($container);
        foreach ($container as $dependencyIndex => $dependency) {
            fwrite($fp, sprintf("Compiled: %s\n", $dependencyIndex));
            try {
                $code = $this->dependencyCompiler->getCode($dependency);
            } catch (Unbound $e) {
                fwrite($fp, sprintf("\nError: %s\nUnbound: %s\n", $dependencyIndex, $e->getMessage()));

                throw $e;
            }

            ($this->dependencySaver)($dependencyIndex, $code);
        }

        fclose($fp);
    }

    public function dumpGraph(): void
    {
        $dumper = new GraphDumper($this->container, $this->scriptDir);
        $dumper();
    }

    public function savePointcuts(Container $container): void
    {
        $ref = (new ReflectionProperty($container, 'pointcuts'));
        $ref->setAccessible(true);
        $pointcuts = $ref->getValue($container);
        ($this->filePutContents)($this->scriptDir . ScriptInjector::AOP, serialize($pointcuts));
    }
}
