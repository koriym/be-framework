<?php

declare(strict_types=1);

namespace Ray\Framework;

use InvalidArgumentException;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use Ray\Di\InjectorInterface;
use Ray\InputQuery\Attribute\Input;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;

use function get_object_vars;
use function sprintf;

/**
 * Resolves constructor arguments for metamorphosis transformations
 *
 * Implements Ray.Framework's philosophy of explicit dependency declaration:
 * - All constructor parameters must have either #[Input] or #[Inject] attributes
 * - #[Input] parameters are resolved from the current object's properties
 * - #[Inject] parameters are resolved from the DI container
 * - Object properties are preserved as-is (no flattening)
 * - Supports #[Named] for DI resolution
 */
final class BecomingArguments implements BecomingArgumentsInterface
{
    public function __construct(
        private InjectorInterface $injector,
    ) {
    }

    public function __invoke(object $current, string $becoming): array
    {
        $properties = get_object_vars($current);
        $targetClass = new ReflectionClass($becoming);
        $constructor = $targetClass->getConstructor();

        if ($constructor === null) {
            return [];
        }

        $args = [];
        foreach ($constructor->getParameters() as $param) {
            $this->validateParameterAttributes($param);

            if (! empty($param->getAttributes(Input::class))) {
                // #[Input] - resolve from the current object's properties
                $args[$param->getName()] = $this->resolveInputParameter($param, $properties);
            } elseif (! empty($param->getAttributes(Inject::class))) {
                // #[Inject] - resolve from DI container
                $args[$param->getName()] = $this->resolveInjectParameter($param);
            }
        }

        return $args;
    }

    /**
     * Resolves #[Input] parameters from the current object's properties
     */
    private function resolveInputParameter(ReflectionParameter $param, array $properties): mixed
    {
        $paramName = $param->getName();

        if (isset($properties[$paramName])) {
            return $properties[$paramName];
        }

        if ($param->isDefaultValueAvailable()) {
            return $param->getDefaultValue();
        }

        throw new InvalidArgumentException(
            sprintf(
                'Required #[Input] parameter "%s" is missing from object properties in %s::%s',
                $paramName,
                $param->getDeclaringClass()->getName(),
                $param->getDeclaringFunction()->getName(),
            ),
        );
    }

    /**
     * Resolves #[Inject] parameters from DI container
     *
     * Supports #[Named] attributes for named bindings.
     * Scalar types require #[Named] or default values (Ray.Di historical compatibility).
     */
    private function resolveInjectParameter(ReflectionParameter $param): mixed
    {
        $type = $param->getType();

        if (! $type instanceof ReflectionNamedType) {
            throw new InvalidArgumentException(
                sprintf('Cannot resolve union/intersection types for parameter "%s"', $param->getName()),
            );
        }

        // Check for #[Named] attribute
        $namedAttributes = $param->getAttributes(Named::class);
        $namedValue = null;
        if (! empty($namedAttributes)) {
            $named = $namedAttributes[0]->newInstance();
            $namedValue = $named->value;
        }

        // Handle scalar types (Ray.Di historical compatibility - PHP 5.4+)
        if ($type->isBuiltin()) {
            if ($namedValue !== null) {
                return $this->injector->getInstance('', $namedValue);
            }

            // Scalar type without #[Named] - use default value or throw
            if ($param->isDefaultValueAvailable()) {
                return $param->getDefaultValue();
            }

            throw new InvalidArgumentException(
                sprintf(
                    'Scalar parameter "%s" requires #[Named] attribute or default value',
                    $param->getName(),
                ),
            );
        }

        // Object type
        $className = $type->getName();

        if ($namedValue !== null) {
            return $this->injector->getInstance($className, $namedValue);
        }

        return $this->injector->getInstance($className);
    }

    /**
     * Validates that all constructor parameters have explicit attribute declarations
     *
     * Enforces Ray.Framework's philosophy: "Describe Yourself (Well)"
     * All dependencies must be explicitly declared for clarity and safety.
     */
    private function validateParameterAttributes(ReflectionParameter $param): void
    {
        $hasInput = ! empty($param->getAttributes(Input::class));
        $hasInject = ! empty($param->getAttributes(Inject::class));

        if (! $hasInput && ! $hasInject) {
            throw new InvalidArgumentException(
                sprintf(
                    'Parameter "%s" in %s::%s must have either #[Input] or #[Inject] attribute. ' .
                    'Ray.Framework requires explicit dependency declaration for safety and clarity. ' .
                    'Use #[Input] if this should come from the previous object, ' .
                    'or #[Inject] if this should come from the DI container.',
                    $param->getName(),
                    $param->getDeclaringClass()->getName(),
                    $param->getDeclaringFunction()->getName(),
                ),
            );
        }

        if ($hasInput && $hasInject) {
            throw new InvalidArgumentException(
                sprintf(
                    'Parameter "%s" in %s::%s cannot have both #[Input] and #[Inject] attributes simultaneously. ' .
                    'These attributes are mutually exclusive to ensure clear and unambiguous parameter semantics.',
                    $param->getName(),
                    $param->getDeclaringClass()->getName(),
                    $param->getDeclaringFunction()->getName(),
                ),
            );
        }
    }
}
