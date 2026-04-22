<?php

declare(strict_types=1);

namespace Be\Framework\SemanticLog;

use ReflectionClass;

use function array_walk;
use function get_object_vars;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_object;
use function is_string;

/**
 * Safely extracts public object properties for semantic log payloads.
 */
final class ObjectPropertyExtractor
{
    /**
     * @return ObjectProperties
     * @phpstan-return array<string, mixed>
     */
    public function extract(object $result): array
    {
        $properties = $this->collectVisibleProperties($result);
        $this->mergeDeclaredProperties($properties, $result);

        return $properties;
    }

    /** @return array<string, mixed> */
    private function collectVisibleProperties(object $result): array
    {
        $properties = [];
        $dynamicProperties = get_object_vars($result);
        array_walk(
            $dynamicProperties,
            static function (mixed $value, string $name) use (&$properties): void {
                if ($value instanceof Been || ! self::isStorableValue($value)) {
                    return;
                }

                self::storeProperty($properties, $name, $value);
            },
        );

        return $properties;
    }

    /** @param array<string, mixed> $properties */
    private function mergeDeclaredProperties(array &$properties, object $result): void
    {
        foreach ((new ReflectionClass($result))->getProperties() as $property) {
            if (! $property->isPublic() || $property->isStatic()) {
                continue;
            }

            $name = $property->getName();
            if (! $property->isInitialized($result)) {
                $properties[$name] = null;

                continue;
            }

            /** @psalm-suppress MixedAssignment */
            $value = $property->getValue($result);
            if ($value instanceof Been) {
                unset($properties[$name]);

                continue;
            }

            if (! self::isStorableValue($value)) {
                continue;
            }

            self::storeProperty($properties, $name, $value);
        }
    }

    /** @psalm-assert-if-true array<array-key, mixed>|bool|float|int|object|string|null $value */
    private static function isStorableValue(mixed $value): bool
    {
        return $value === null
            || is_array($value)
            || is_bool($value)
            || is_float($value)
            || is_int($value)
            || is_object($value)
            || is_string($value);
    }

    /**
     * @param array<string, mixed>                                      $properties
     * @param array<array-key, mixed>|bool|float|int|object|string|null $value
     */
    private static function storeProperty(array &$properties, string $name, array|bool|float|int|object|string|null $value): void
    {
        $properties[$name] = $value;
    }
}
