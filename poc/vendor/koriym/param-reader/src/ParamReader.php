<?php

declare(strict_types=1);

namespace Koriym\ParamReader;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;
use ReflectionProperty;

use function assert;

use const PHP_VERSION_ID;

/**
 * {@inheritDoc}
 *
 * @template T of object
 */
final class ParamReader implements ParamReaderInterface
{
    /** @var ?Reader */
    private $reader;

    public function __construct(?Reader $reader = null)
    {
        $this->reader = $reader;
    }

    /**
     * @return array<T>
     */
    public function getParametrAnnotations(ReflectionParameter $param): array
    {
        if (PHP_VERSION_ID < 80000) {
            return $this->readAnnotations($param);
        }

        /** @var array<ReflectionAttribute> $attributes */
        $attributes = $param->getAttributes();
        if ($attributes === []) {
            return $this->readAnnotations($param);
        }

        $instances = [];
        foreach ($attributes as $attribute) {
            /** @var array<T> $instance */
            $instance = $attribute->newInstance();
            $instances[] = $instance;
        }

        /** @var array<T> $instances */
        return $instances;
    }

    /**
     * {@inheritDoc}
     *
     * @param class-string<T> $annotation
     *
     * @return T|null
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function getParametrAnnotation(ReflectionParameter $param, string $annotation): ?object
    {
        if (PHP_VERSION_ID < 80000) {
            return $this->readAnnotation($param, $annotation);
        }

        /** @var array<ReflectionAttribute> $attributes */
        $attributes = $param->getAttributes($annotation);
        if ($attributes === []) {
            return $this->readAnnotation($param, $annotation);
        }

        $attribute = $attributes[0];
        /** @var T $instance */
        $instance = $attribute->newInstance();

        return $instance;
    }

    /**
     * @return array<T>
     */
    private function readAnnotations(ReflectionParameter $param)
    {
        $prop = $this->getProp($param);
        if ($prop === null) {
            return [];
        }

        /** @var array<T> $instances */
        $instances = ($this->reader ?? new AnnotationReader())->getPropertyAnnotations($prop);

        return $instances;
    }

    /**
     * @param class-string<T> $class
     *
     * @return T|null
     */
    private function readAnnotation(ReflectionParameter $param, string $class)
    {
        $prop = $this->getProp($param);
        if ($prop === null) {
            return null;
        }

        return ($this->reader ?? new AnnotationReader())->getPropertyAnnotation($prop, $class);
    }

    private function getProp(ReflectionParameter $param): ?ReflectionProperty
    {
        $ref = $param->getDeclaringClass();
        assert($ref instanceof ReflectionClass);
        try {
            return new ReflectionProperty($ref->getName(), $param->getName());
        } catch (ReflectionException $e) {
            return null;
        }
    }
}
