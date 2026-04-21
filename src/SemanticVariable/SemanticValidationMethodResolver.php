<?php

declare(strict_types=1);

namespace Be\Framework\SemanticVariable;

use Be\Framework\Attribute\Validate;
use Be\Framework\Types;
use Ray\Di\Di\Inject;
use Ray\InputQuery\Attribute\Input;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;

use function array_filter;
use function array_key_exists;
use function array_slice;
use function array_values;
use function count;
use function end;
use function explode;
use function in_array;

/**
 * Resolves validation methods and their arguments from semantic classes.
 *
 * @psalm-import-type ParameterAttributes from Types
 * @psalm-import-type ValidationArguments from Types
 * @psalm-import-type ReflectionMethods from Types
 */
final class SemanticValidationMethodResolver
{
    public function hasInjectAttribute(ReflectionParameter $parameter): bool
    {
        return ! empty($parameter->getAttributes(Inject::class));
    }

    /** @return ParameterAttributes */
    public function extractAttributeNames(ReflectionParameter $parameter): array
    {
        $attributeNames = [];

        foreach ($parameter->getAttributes() as $attribute) {
            $className = $attribute->getName();
            if ($className === Input::class || $className === Inject::class) {
                continue;
            }

            $parts = explode('\\', $className);
            $attributeNames[] = end($parts);
        }

        return $attributeNames;
    }

    /**
     * @param ParameterAttributes $parameterAttributes
     * @param ValidationArguments $validationArgs
     *
     * @return ReflectionMethods
     * @phpstan-return array<int, ReflectionMethod>
     */
    public function getMatchingValidationMethods(object $semanticClass, array $parameterAttributes, array $validationArgs): array
    {
        $reflection = new ReflectionClass($semanticClass);
        $matchingMethods = [];

        foreach ($reflection->getMethods() as $method) {
            if (! $this->isMatchingValidationMethod($method, $parameterAttributes, $validationArgs)) {
                continue;
            }

            $matchingMethods[] = $method;
        }

        return $matchingMethods;
    }

    /**
     * @param array<string, mixed> $allArgs
     *
     * @return ValidationArguments|null
     */
    public function matchArgsByName(ReflectionMethod $method, array $allArgs): array|null
    {
        $nonInjectParams = $this->getNonInjectParameters($method);
        if (count($nonInjectParams) < 2) {
            return null;
        }

        $methodArgs = [];
        foreach ($nonInjectParams as $param) {
            if (! array_key_exists($param->getName(), $allArgs)) {
                return null;
            }

            /** @psalm-suppress MixedAssignment */
            $methodArgs[] = $allArgs[$param->getName()];
        }

        return $methodArgs;
    }

    /**
     * @param ValidationArguments $inputArgs
     *
     * @return ValidationArguments
     */
    public function resolveMethodArguments(ReflectionMethod $method, array $inputArgs): array
    {
        return array_values(array_slice($inputArgs, 0, count($this->getNonInjectParameters($method))));
    }

    /**
     * @param ParameterAttributes $parameterAttributes
     * @param ValidationArguments $validationArgs
     */
    private function isMatchingValidationMethod(ReflectionMethod $method, array $parameterAttributes, array $validationArgs): bool
    {
        return $this->isValidateMethod($method)
            && $this->hasEnoughArguments($method, $validationArgs)
            && $this->matchesParameterAttributes($method, $parameterAttributes);
    }

    private function isValidateMethod(ReflectionMethod $method): bool
    {
        return ! empty($method->getAttributes(Validate::class));
    }

    /** @param ValidationArguments $validationArgs */
    private function hasEnoughArguments(ReflectionMethod $method, array $validationArgs): bool
    {
        return count($validationArgs) >= count($this->getNonInjectParameters($method));
    }

    /** @param ParameterAttributes $parameterAttributes */
    private function matchesParameterAttributes(ReflectionMethod $method, array $parameterAttributes): bool
    {
        foreach ($this->getNonInjectParameters($method) as $parameter) {
            if (! $this->parameterAttributesMatch($parameter, $parameterAttributes)) {
                return false;
            }
        }

        return true;
    }

    /** @param ParameterAttributes $parameterAttributes */
    private function parameterAttributesMatch(ReflectionParameter $parameter, array $parameterAttributes): bool
    {
        $requiredAttributes = $this->extractAttributeNames($parameter);
        if ($requiredAttributes === []) {
            return true;
        }

        foreach ($requiredAttributes as $attribute) {
            if (in_array($attribute, $parameterAttributes, true)) {
                return true;
            }
        }

        return false;
    }

    /** @return list<ReflectionParameter> */
    private function getNonInjectParameters(ReflectionMethod $method): array
    {
        $nonInjectParameters = array_values(array_filter(
            $method->getParameters(),
            fn (ReflectionParameter $parameter): bool => ! $this->hasInjectAttribute($parameter),
        ));

        /** @var list<ReflectionParameter> $nonInjectParameters */
        return $nonInjectParameters;
    }
}
