<?php

declare(strict_types=1);

namespace Ray\Compiler;

use Ray\Di\AbstractModule;

/**
 * Serializable lazy module
 */
interface LazyModuleInterface
{
    public function __invoke(): AbstractModule;
}
