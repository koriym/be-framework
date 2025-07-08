<?php

declare(strict_types=1);

namespace Ray\Compiler;

use Ray\Di\AbstractModule;
use Ray\Di\MultiBinding\Map;
use Ray\Di\MultiBinding\MapProvider;

/** @deprecated Use CompilerModule */
class MapModule extends AbstractModule
{
    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this->bind(Map::class)->toProvider(MapProvider::class);
    }
}
