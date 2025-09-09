<?php

declare(strict_types=1);

namespace Be\Framework;

use Ray\Di\Di\Inject;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;

use function array_key_exists;
use function array_map;
use function assert;
use function get_object_vars;
use function gettype;
use function implode;
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

            // Skip parameters with #[Inject] attribute - they will be resolved by DI container
            $attributes = $param->getAttributes(Inject::class);
            if (! empty($attributes)) {
                continue;
            }

            if (! array_key_exists($paramName, $currentProperties)) {
                return false; // Required property missing
            }

            $paramType = $param->getType();
            /** @psalm-suppress  MixedAssignment */
            $actualValue = $currentProperties[$paramName];

            if ($paramType !== null && ! $this->isValueCompatibleWithType($actualValue, $paramType)) {
                return false; // Type mismatch
            }
        }

        return true;
    }

    /**
     * Get detailed mismatch information for debugging
     *
     * @param class-string $class
     *
     * @return array<string, string> Parameter name => mismatch reason
     */
    public function getMismatchReasons(object $current, string $class): array
    {
        $reasons = [];
        $reflection = new ReflectionClass($class);
        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            return $reasons;
        }

        $currentProperties = get_object_vars($current);

        foreach ($constructor->getParameters() as $param) {
            $paramName = $param->getName();

            // Skip parameters with #[Inject] attribute
            $attributes = $param->getAttributes(Inject::class);
            if (! empty($attributes)) {
                continue;
            }

            if (! array_key_exists($paramName, $currentProperties)) {
                if (! $param->isDefaultValueAvailable() && ! $param->isOptional()) {
                    $reasons[$paramName] = 'Property missing from source object';
                }
                continue;
            }

            $paramType = $param->getType();
            /** @psalm-suppress  MixedAssignment */
            $actualValue = $currentProperties[$paramName];

            if ($paramType !== null && ! $this->isValueCompatibleWithType($actualValue, $paramType)) {
                $expectedType = $this->getTypeDescription($paramType);
                $actualType = $this->getValueTypeDescription($actualValue);
                $reasons[$paramName] = "Type mismatch: expected {$expectedType}, got {$actualType}";
            }
        }

        return $reasons;
    }

    /**
     * Get human-readable description of a ReflectionType
     */
    private function getTypeDescription(ReflectionType $type): string
    {
        if ($type instanceof ReflectionNamedType) {
            return ($type->allowsNull() ? '?' : '') . $type->getName();
        }

        if ($type instanceof ReflectionUnionType) {
            $types = array_map(fn (ReflectionType $t) => $this->getTypeDescription($t), $type->getTypes());

            return implode('|', $types);
        }

        assert($type instanceof ReflectionIntersectionType, 'Unknown ReflectionType encountered');
        $types = array_map(fn (ReflectionType $t) => $this->getTypeDescription($t), $type->getTypes());

        return implode('&', $types);
    }

    /**
     * Get human-readable description of a value's type
     */
    private function getValueTypeDescription(mixed $value): string
    {
        if ($value === null) {
            return 'null';
        }

        if (is_object($value)) {
            return $value::class;
        }

        return gettype($value);
    }

    /**
     * Check if a value is compatible with a ReflectionType
     */
    private function isValueCompatibleWithType(mixed $value, ReflectionType $type): bool
    {
        if ($type instanceof ReflectionNamedType) {
            return $this->handleNamedType($value, $type);
        }

        if ($type instanceof ReflectionUnionType) {
            return $this->handleUnionType($value, $type);
        }

        assert($type instanceof ReflectionIntersectionType, 'Unknown ReflectionType encountered');

        return $this->handleIntersectionType($value, $type);
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
