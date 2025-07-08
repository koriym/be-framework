<?php

declare(strict_types=1);

namespace Ray\Compiler;

use Ray\Compiler\Annotation\Compile;
use Ray\Di\AbstractModule;
use Ray\Di\Annotation\ScriptDir;
use Ray\Di\BuiltinModule;
use Ray\Di\InjectorInterface;
use Ray\Di\Scope;

use function sprintf;

class CompilerModule extends AbstractModule
{
    /** @var string */
    private $scriptDir;

    public function __construct(string $scriptDir, ?AbstractModule $module = null)
    {
        $this->scriptDir = $scriptDir;

        parent::__construct($module);
    }

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this->bind()->annotatedWith(Compile::class)->toInstance(true);
        $this->install((new BuiltinModule())($this));
        $this->bind('')->annotatedWith(ScriptDir::class)->toInstance($this->scriptDir);
        $this->bind(InjectorInterface::class)->to(CompiledInjector::class)->in(Scope::SINGLETON);
        (new FilePutContents())(sprintf('%s/_bindings.log', $this->scriptDir), (string) $this);
    }
}
