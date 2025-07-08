<?php

declare(strict_types=1);

namespace Ray\Compiler;

use Ray\Di\AbstractModule;
use Ray\Di\InjectorInterface;

/**
 * @psalm-import-type ScriptDir from CompileInjector
 * @deprecated 
 */
class ScriptInjectorModule extends AbstractModule
{
    /** @var ScriptDir */
    private $scriptDir;

    /** @param ScriptDir $scriptDir */
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
        $this->bind(InjectorInterface::class)->toInstance(new ScriptInjector($this->scriptDir));
    }
}
