<?php

declare(strict_types=1);

namespace Be\Framework\SemanticLog;

use ReflectionClass;
use SensitiveParameter;

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
 *
 * Handles both declared and dynamic properties, with special handling for:
 * - Uninitialized properties (returns null)
 * - Been instances (excluded from output)
 * - Non-storable values like resources (excluded)
 * - Constructor parameters marked with PHP's `#[\SensitiveParameter]`:
 *   the matching public property value is replaced with `self::REDACTED`.
 */
final class ObjectPropertyExtractor
{
    /**
     * Placeholder written into the log payload in place of a value whose
     * corresponding constructor parameter is marked `#[\SensitiveParameter]`.
     */
    public const string REDACTED = '[REDACTED]';

    /**
     * Extract all public properties from an object for logging
     *
     * @param object $result The object to extract properties from
     *
     * @return ObjectProperties Key-value pairs of property names and their values.
     *                          Uninitialized properties are returned as null.
     *                          Been instances and non-JSON-serializable values are excluded.
     *                          Values for properties whose matching constructor
     *                          parameter carries `#[\SensitiveParameter]` are
     *                          replaced with `self::REDACTED`.
     * @phpstan-return array<string, mixed>
     */
    public function extract(object $result): array
    {
        $sensitive = self::collectSensitiveParameterNames($result);
        $properties = $this->collectVisibleProperties($result, $sensitive);
        $this->mergeDeclaredProperties($properties, $result, $sensitive);

        return $properties;
    }

    /**
     * @param array<string, true> $sensitive
     *
     * @return array<string, mixed>
     */
    private function collectVisibleProperties(object $result, array $sensitive): array
    {
        $properties = [];
        $dynamicProperties = get_object_vars($result);
        array_walk(
            $dynamicProperties,
            static function (mixed $value, string $name) use (&$properties, $sensitive): void {
                if ($value instanceof Been || ! self::isStorableValue($value)) {
                    return;
                }

                self::storeProperty($properties, $name, isset($sensitive[$name]) ? self::REDACTED : $value);
            },
        );

        return $properties;
    }

    /**
     * @param array<string, mixed> $properties
     * @param array<string, true>  $sensitive
     */
    private function mergeDeclaredProperties(array &$properties, object $result, array $sensitive): void
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

            self::storeProperty($properties, $name, isset($sensitive[$name]) ? self::REDACTED : $value);
        }
    }

    /**
     * Collect parameter names of the object's constructor that carry `#[\SensitiveParameter]`.
     *
     * Be Framework's idiomatic pattern is `public readonly` properties that
     * mirror constructor parameter names, so a sensitive parameter implies the
     * same-named property carries a secret.
     *
     * @return array<string, true>
     */
    private static function collectSensitiveParameterNames(object $result): array
    {
        $constructor = (new ReflectionClass($result))->getConstructor();
        if ($constructor === null) {
            return [];
        }

        $sensitive = [];
        foreach ($constructor->getParameters() as $param) {
            if ($param->getAttributes(SensitiveParameter::class) === []) {
                continue;
            }

            $sensitive[$param->getName()] = true;
        }

        return $sensitive;
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
