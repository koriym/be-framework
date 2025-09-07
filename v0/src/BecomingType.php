<?php

declare(strict_types=1);

namespace Be\Framework;

use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;

use function array_key_exists;
use function get_object_vars;
use function gettype;
use function is_object;

/**
 * Type compatibility and resolution utilities for the Becoming framework
 *
 * This class provides type checking and compatibility validation for object metamorphosis,
 * supporting both scalar types and class types, including union types.
 */
final class BecomingType
{
    /**
     * $currentのプロパティが$classクラスのコンストラクタに代入可能かどうかを判定する
     *
     * @param class-string $class
     */
    public function match(object $current, string $class): bool
    {
        $reflection = new ReflectionClass($class);
        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            return true; // No constructor parameters to match
        }

        $currentProperties = get_object_vars($current);

        foreach ($constructor->getParameters() as $param) {
            $paramName = $param->getName();

            if (! array_key_exists($paramName, $currentProperties)) {
                return false; // Required property missing
            }

            $paramType = $param->getType();
            $actualValue = $currentProperties[$paramName];

            if ($paramType !== null && ! $this->isValueCompatibleWithType($actualValue, $paramType)) {
                return false; // Type mismatch
            }
        }

        return true;
    }

    /**
     * Check if a value is compatible with a ReflectionType
     */
    private function isValueCompatibleWithType(mixed $value, ReflectionType $type): bool
    {
        if ($type instanceof ReflectionUnionType) {
            return $this->handleUnionType($value, $type);
        }

        if ($type instanceof ReflectionIntersectionType) {
            return $this->handleIntersectionType($value, $type);
        }

        if ($type instanceof ReflectionNamedType) {
            return $this->handleNamedType($value, $type);
        }

        return false;
    }

    /**
     * Handle ReflectionUnionType (e.g., int|string)
     */
    private function handleUnionType(mixed $value, ReflectionUnionType $type): bool
    {
        foreach ($type->getTypes() as $unionType) {
            if ($this->isValueCompatibleWithType($value, $unionType)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Handle ReflectionIntersectionType (e.g., A&B)
     */
    private function handleIntersectionType(mixed $value, ReflectionIntersectionType $type): bool
    {
        foreach ($type->getTypes() as $intersectionType) {
            if (! $this->isValueCompatibleWithType($value, $intersectionType)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Handle ReflectionNamedType (e.g., string, int, MyClass)
     */
    private function handleNamedType(mixed $value, ReflectionNamedType $type): bool
    {
        $typeName = $type->getName();

        // Handle null values
        if ($value === null) {
            return $type->allowsNull();
        }

        // Handle built-in types
        $actualType = $this->getValueType($value);

        if ($this->isBuiltInTypeCompatible($actualType, $typeName)) {
            return true;
        }

        // Handle object types
        if (is_object($value)) {
            // Special handling for 'object' type hint
            if ($typeName === 'object') {
                return true;
            }

            return $value instanceof $typeName;
        }

        return false;
    }

    /**
     * Get the type of a value as a string
     */
    private function getValueType(mixed $value): string
    {
        if (is_object($value)) {
            return $value::class;
        }

        return match (gettype($value)) {
            'integer' => 'int',
            'boolean' => 'bool',
            'double' => 'float',
            default => gettype($value)
        };
    }

    /**
     * Check if built-in types are compatible
     */
    private function isBuiltInTypeCompatible(string $actualType, string $expectedType): bool
    {
        // Direct match
        if ($actualType === $expectedType) {
            return true;
        }

        // Handle mixed type
        return $expectedType === 'mixed';
    }
}
