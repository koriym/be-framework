<?php

declare(strict_types=1);

namespace Ray\Compiler;

use Ray\Aop\Compiler;
use Ray\Aop\Pointcut;
use Ray\Compiler\Exception\Unbound;
use Ray\Di\AbstractModule;
use Ray\Di\Annotation\ScriptDir;
use Ray\Di\Bind;
use Ray\Di\Dependency;
use Ray\Di\Exception\NotFound;
use function assert;
use function error_reporting;
use function explode;
use function file_exists;
use function file_get_contents;
use function is_bool;
use function unserialize;
use const E_NOTICE;

/**
 * @psalm-import-type ScriptDir from CompileInjector
 * @psalm-type Pointcuts = list<Pointcut>
 * @deprecated
 */
final class OnDemandCompiler
{
    /** @var ScriptDir */
    private $scriptDir;

    /** @var ScriptInjector */
    private $injector;

    /** @var AbstractModule */
    private $module;

    /** @var CompileNullObject */
    private $compiler;

    /** @param ScriptDir $scriptDir */
    public function __construct(ScriptInjector $injector, string $scriptDir, AbstractModule $module)
    {
        $this->scriptDir = $scriptDir;
        $this->injector = $injector;
        $this->module = $module;
        $this->compiler = new CompileNullObject();
    }

    /**
     * Compile dependency on demand
     */
    public function __invoke(string $dependencyIndex): void
    {
        [$class] = explode('-', $dependencyIndex);
        $containerObject = $this->module->getContainer();
        try {
            new Bind($containerObject, $class);
        } catch (NotFound $e) {
            throw new Unbound($dependencyIndex, 0, $e);
        }

        $containerArray = $containerObject->getContainer();
        if (! isset($containerArray[$dependencyIndex])) {
            throw new Unbound($dependencyIndex, 0);
        }

        ($this->compiler)($containerObject, $this->scriptDir);
        $dependency = $containerArray[$dependencyIndex];
        /** @var Pointcut $pointCuts */
        $pointCuts = $this->loadPointcuts();
        $isWeaverable = $dependency instanceof Dependency && ! empty($pointCuts);
        if ($isWeaverable) {
            $dependency->weaveAspects(new Compiler($this->scriptDir), $pointCuts);
        }

        $code = (new DependencyCode($containerObject, $this->injector))->getCode($dependency);
        (new DependencySaver($this->scriptDir))($dependencyIndex, $code);
    }

    /** @return Pointcut */
    private function loadPointcuts(): array
    {
        $pointcutsPath = $this->scriptDir . ScriptInjector::AOP;
        if (! file_exists($pointcutsPath)) {
            return []; // @codeCoverageIgnore
        }

        $serialized = file_get_contents($pointcutsPath);
        assert(! is_bool($serialized));
        $er = error_reporting(error_reporting() ^ E_NOTICE);
        /** @var Pointcut $pointcuts */
        $pointcuts = unserialize($serialized, ['allowed_classes' => true]);
        error_reporting($er);

        return $pointcuts;
    }
}
