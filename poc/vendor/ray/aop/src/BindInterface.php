<?php

declare(strict_types=1);

namespace Ray\Aop;

/**
 * @psalm-import-type MethodName from Types
 * @psalm-import-type MethodBindings from Types
 */
interface BindInterface
{
    /**
     * Bind pointcuts
     *
     * @param class-string $class     class name
     * @param Pointcut[]   $pointcuts Pointcut array
     */
    public function bind(string $class, array $pointcuts): self;

    /**
     * Bind interceptors to method
     *
     * @param MethodName          $method
     * @param MethodInterceptor[] $interceptors
     */
    public function bindInterceptors(string $method, array $interceptors): self;

    /**
     * Return bindings data
     *
     * [$methodNameA => [$interceptorA, ...][]
     *
     * @return MethodBindings
     */
    public function getBindings();

    /**
     * Return hash
     */
    public function __toString(): string;
}
