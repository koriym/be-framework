<?php

declare(strict_types=1);

namespace Ray\Compiler;

use Ray\Aop\ReflectionClass;
use Ray\Aop\ReflectionMethod;
use Ray\Di\Di\Qualifier;
use Ray\Di\InjectionPointInterface;
use Ray\ServiceLocator\ServiceLocator;
use ReflectionException;
use ReflectionParameter;

use function assert;
use function class_exists;

/**
 * @psalm-import-type ScriptDir from Types
 * @psalm-import-type Ip from Types
 */
final class InjectionPoint implements InjectionPointInterface
{
    /** @var ReflectionParameter */
    private $parameter;

    /** @deprecated use getInstance */
    public function __construct(ReflectionParameter $parameter)
    {
        $this->parameter = $parameter;
    }

    /**
     * @param Ip $ip
     *
     * @throws ReflectionException
     */
    public static function getInstance(array $ip): self
    {
        return new self(new ReflectionParameter([$ip[0], $ip[1]], $ip[2]));
    }

    /**
     * {@inheritDoc}
     */
    public function getParameter(): ReflectionParameter
    {
        return $this->parameter;
    }

    /**
     * {@inheritDoc}
     */
    public function getMethod(): ReflectionMethod
    {
        $this->parameter = $this->getParameter();
        $class = $this->parameter->getDeclaringClass();
        $method = $this->parameter->getDeclaringFunction()->getShortName();
        assert($class instanceof \ReflectionClass);
        assert(class_exists($class->getName()));

        return new ReflectionMethod($class->getName(), $method);
    }

    /**
     * {@inheritDoc}
     */
    public function getClass(): ReflectionClass
    {
        $class = $this->parameter->getDeclaringClass();
        assert($class instanceof \ReflectionClass);

        return new ReflectionClass($class->getName());
    }

    /**
     * {@inheritDoc}
     *
     * @return array<(object|null)>
     *
     * @psalm-suppress ImplementedReturnTypeMismatch
     */
    public function getQualifiers(): array
    {
        return [$this->getQualifier()];
    }

    /**
     * {@inheritDoc}
     *
     * @return object|null
     */
    public function getQualifier()
    {
        $reader = ServiceLocator::getReader();
        $annotations = $reader->getMethodAnnotations($this->getMethod());
        foreach ($annotations as $annotation) {
            $maybeQualifers = $reader->getClassAnnotations(new \ReflectionClass($annotation));
            foreach ($maybeQualifers as $maybeQualifer) {
                if ($maybeQualifer instanceof Qualifier) {
                    return $annotation;
                }
            }
        }

        return null;
    }
}
