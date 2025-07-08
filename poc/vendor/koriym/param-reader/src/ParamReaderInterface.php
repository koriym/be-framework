<?php

declare(strict_types=1);

namespace Koriym\ParamReader;

use ReflectionParameter;

/**
 * Interface for annotation/attribute readers for method parameter
 *
 * Attempts to read the attribute(s) of the parameter,
 * and if not successful, attempts to read the annotation(s) of the property of the same parameter name.
 *
 * @template T of object
 */
interface ParamReaderInterface
{
    /**
     * Gets the attriburtes/annotations applied to a method parameter.
     *
     * @param ReflectionParameter $param The ReflectionParameter of the parameter
     *                                   from which the annotations/attributes should be read.
     *
     * @return array<T> An array of Annotations/Attributes.
     */
    public function getParametrAnnotations(ReflectionParameter $param): array;

    /**
     * Gets a method parameter attriburte/annotation
     *
     * @param ReflectionParameter $param      The ReflectionParameter of the parameter
     *                                        from which the annotations/attributes should be read.
     * @param class-string<T>     $annotation
     *
     * @return T|null The Annotation/Attribute or NULL, if the requested annotation does not exist.
     */
    public function getParametrAnnotation(ReflectionParameter $param, string $annotation): ?object;
}
