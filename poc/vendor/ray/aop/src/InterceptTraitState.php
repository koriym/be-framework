<?php

declare(strict_types=1);

namespace Ray\Aop;

/** @psalm-import-type MethodBindings from Types */
final class InterceptTraitState
{
    /**
     * @var MethodBindings
     * @readonly
     */
    public $bindings;

    /** @var bool Flag controlling whether aspect interception is active */
    public $isAspect = true;

    /** @param MethodBindings $bindings */
    public function __construct(array $bindings)
    {
        $this->bindings = $bindings;
    }
}
