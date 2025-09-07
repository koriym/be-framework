<?php

declare(strict_types=1);

namespace Be\Framework\Aspect;

use Be\Framework\BecomingType;
use Be\Framework\BeingClass;
use ReflectionClass;
use ReflectionParameter;

use function array_merge_recursive;
use function end;
use function explode;
use function get_object_vars;
use function in_array;
use function is_string;

/**
 * Aspect for discovering semantic constraints that will apply during transformation
 *
 * This class helps solve the code readability issue where constraints like #[English]
 * are defined in target classes but not visible when working with input classes.
 */
final class ConstraintDiscovery
{
    public function __construct(
        private BecomingType $becomingType,
    ) {
    }

    /**
     * Discover all semantic constraints that would apply to this object's transformation
     *
     * @return array<string, array<string>> Map of property names to their constraints
     */
    public function discoverConstraints(object $input): array
    {
        $beingClass = new BeingClass();
        $becoming = $beingClass->fromObject($input);

        if ($becoming === null) {
            return [];
        }

        $constraints = [];

        if (is_string($becoming)) {
            $constraints = $this->extractConstraintsFromClass($becoming, $input);
        } else {
            // Array of possible classes - find matching constraints
            foreach ($becoming as $candidateClass) {
                if ($this->becomingType->match($input, $candidateClass)) {
                    $classConstraints = $this->extractConstraintsFromClass($candidateClass, $input);
                    $constraints = array_merge_recursive($constraints, $classConstraints);
                    break; // Use first matching class
                }
            }
        }

        return $constraints;
    }

    /**
     * Extract semantic constraints from a target class constructor
     */
    private function extractConstraintsFromClass(string $className, object $input): array
    {
        $reflection = new ReflectionClass($className);
        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            return [];
        }

        $constraints = [];
        $inputProperties = get_object_vars($input);

        foreach ($constructor->getParameters() as $parameter) {
            // Skip #[Inject] parameters
            if ($this->hasInjectAttribute($parameter)) {
                continue;
            }

            $paramName = $parameter->getName();

            // Only include constraints for properties that exist in input
            if (isset($inputProperties[$paramName])) {
                $paramConstraints = $this->extractParameterConstraints($parameter);
                if (! empty($paramConstraints)) {
                    $constraints[$paramName] = $paramConstraints;
                }
            }
        }

        return $constraints;
    }

    /**
     * Extract semantic constraint attributes from a parameter
     *
     * @return array<string> List of constraint names
     */
    private function extractParameterConstraints(ReflectionParameter $parameter): array
    {
        $constraints = [];
        $attributes = $parameter->getAttributes();

        foreach ($attributes as $attribute) {
            $attributeName = $attribute->getName();

            // Check if this is a semantic constraint (you could have a registry of known constraints)
            if ($this->isSemanticConstraint($attributeName)) {
                $constraints[] = $this->formatConstraintName($attributeName);
            }
        }

        return $constraints;
    }

    /**
     * Check if an attribute is a semantic constraint
     */
    private function isSemanticConstraint(string $attributeName): bool
    {
        // This could be enhanced with a constraint registry
        $knownConstraints = [
            'Be\\Framework\\Tests\\Fake\\Ontology\\English',
            'Be\\Framework\\Tests\\Fake\\Ontology\\Japanese',
            // Add more as needed
        ];

        return in_array($attributeName, $knownConstraints, true);
    }

    /**
     * Format constraint name for display
     */
    private function formatConstraintName(string $fullClassName): string
    {
        $parts = explode('\\', $fullClassName);

        return end($parts);
    }

    /**
     * Check if parameter has #[Inject] attribute
     */
    private function hasInjectAttribute(ReflectionParameter $parameter): bool
    {
        $attributes = $parameter->getAttributes();

        foreach ($attributes as $attribute) {
            if ($attribute->getName() === 'Be\\Framework\\Attribute\\Inject') {
                return true;
            }
        }

        return false;
    }
}
