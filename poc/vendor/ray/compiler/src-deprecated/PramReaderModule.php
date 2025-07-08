<?php

declare(strict_types=1);

namespace Ray\Compiler;

use Koriym\ParamReader\ParamReader;
use Koriym\ParamReader\ParamReaderInterface;
use Ray\Di\AbstractModule;

/** @deprecated Use CompilerModule */
class PramReaderModule extends AbstractModule
{
    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this->bind(ParamReaderInterface::class)->to(ParamReader::class);
    }
}
