<?php

declare(strict_types=1);

namespace Ray\Compiler;

use Ray\Compiler\Annotation\Compile;
use Ray\Di\AbstractModule;

/** @deprecated Use CompilerModule */
class DiCompileModule extends AbstractModule
{
    /** @var bool */
    private $doCompile;

    public function __construct(bool $doCompile, ?AbstractModule $module = null)
    {
        $this->doCompile = $doCompile;

        parent::__construct($module);
    }

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this->bind()->annotatedWith(Compile::class)->toInstance($this->doCompile);
    }
}
