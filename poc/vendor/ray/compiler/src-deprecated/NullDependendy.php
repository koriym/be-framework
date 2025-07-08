<?php

namespace Ray\Compiler;

use Ray\Di\Bind;
use Ray\Di\Container;
use Ray\Di\DependencyInterface;

final class NullDependendy implements DependencyInterface
{
    public function __toString()
    {
        // TODO: Implement __toString() method.
    }

    public function inject(Container $container)
    {
        // TODO: Implement inject() method.
    }

    public function register(array &$container, Bind $bind)
    {
        // TODO: Implement register() method.
    }

    public function setScope($scope)
    {
        // TODO: Implement setScope() method.
    }

}
