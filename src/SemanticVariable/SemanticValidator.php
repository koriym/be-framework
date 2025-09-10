<?php

declare(strict_types=1);

namespace Be\Framework\SemanticVariable;

use Be\Framework\Attribute\SemanticTag;
use Be\Framework\Attribute\Validate;
use Be\Framework\Exception\SemanticVariableException;
use Be\Framework\Types;
use DomainException;
use Override;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use Ray\InputQuery\Attribute\Input;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;

use function array_filter;
use function array_key_exists;
use function array_values;
use function class_exists;
use function count;
use function end;
use function explode;
use function get_object_vars;
use function in_array;
use function str_replace;
use function trigger_error;
use function ucwords;

use const E_USER_NOTICE;

/**
 * Validates semantic variables based on their names
 *
 * Automatically resolves validation classes from variable names and
 * executes appropriate validation methods based on argument patterns.
 *
 * @psalm-import-type ConstructorArguments from Types
 * @psalm-import-type ParameterAttributes from Types
 * @psalm-import-type ValidationArguments from Types
 * @psalm-import-type ReflectionMethods from Types
 * @psalm-import-type ExceptionCollection from Types
 */
final class SemanticValidator implements SemanticValidatorInterface
{
    public function __construct(
        #[Named('semantic_namespace')]
        private readonly string $semanticNamespace,
    ) {
    }

    /**
     * Validate object properties based on constructor parameter names
     *
     * For each constructor parameter, validate the corresponding object property
     * using semantic variable validation. For example, if constructor has $age parameter,
     * validate $object->age using Age semantic constraint.
     *
     * @param ReflectionMethod $constructor Constructor method with parameter definitions
     * @param object           $object      Object containing properties to validate
     *
     * @return Errors Validation errors (empty if validation passes)
     */
    public function validateProps(ReflectionMethod $constructor, object $object): Errors
    {
        $allErrors = [];
        $objectProperties = get_object_vars($object);

        foreach ($constructor->getParameters() as $parameter) {
            // Skip #[Inject] parameters
            if ($this->hasInjectAttribute($parameter)) {
                continue;
            }

            $paramName = $parameter->getName();

            // Skip if object doesn't have corresponding property
            if (! array_key_exists($paramName, $objectProperties)) {
                continue;
            }

            /** @psalm-suppress MixedAssignment */
            $propertyValue = $objectProperties[$paramName];
            $parameterAttributes = $this->extractAttributeNames($parameter);

            // Validate property using semantic variable validation
            $errors = $this->validateWithAttributes($paramName, $parameterAttributes, $propertyValue);

            if ($errors->hasErrors()) {
                $allErrors = [...$allErrors, ...$errors->exceptions];
            }
        }

        return empty($allErrors) ? new NullErrors() : new Errors($allErrors);
    }

    /**
     * Validate all arguments for a method (primary API)
     *
     * @param ReflectionMethod     $method Method containing parameter definitions
     * @param ConstructorArguments $args   Values to validate (associative array: param_name => value)
     *
     * @return Errors Validation errors (empty if validation passes)
     */
    #[Override]
    public function validateArgs(ReflectionMethod $method, array $args): Errors
    {
        $allErrors = [];

        foreach ($method->getParameters() as $parameter) {
            // Skip #[Inject] parameters
            if ($this->hasInjectAttribute($parameter)) {
                continue;
            }

            $name = $parameter->getName();
            if (array_key_exists($name, $args)) {
                $errors = $this->validateArg($parameter, $args[$name]);
                if ($errors->hasErrors()) {
                    $allErrors = [...$allErrors, ...$errors->exceptions];
                }
            }
        }

        return empty($allErrors) ? new NullErrors() : new Errors($allErrors);
    }

    /**
     * Validate single parameter (test convenience API)
     *
     * @param ReflectionParameter $parameter Parameter containing variable name and attributes
     * @param mixed               $value     Value to validate
     *
     * @return Errors Validation errors (empty if validation passes)
     */
    #[Override]
    public function validateArg(ReflectionParameter $parameter, mixed $value): Errors
    {
        $variableName = $parameter->getName();
        $attributes = $this->extractAttributeNames($parameter);

        return $this->validateWithAttributes($variableName, $attributes, $value);
    }

    /**
     * Check if parameter has #[Inject] attribute
     */
    private function hasInjectAttribute(ReflectionParameter $parameter): bool
    {
        return ! empty($parameter->getAttributes(Inject::class));
    }

    /**
     * Extract attribute names from ReflectionParameter
     *
     * @return ParameterAttributes
     */
    private function extractAttributeNames(ReflectionParameter $parameter): array
    {
        $attributeNames = [];

        foreach ($parameter->getAttributes() as $attribute) {
            $className = $attribute->getName();
            if ($className === Input::class || $className === Inject::class) {
                continue; // Skip non-semantic attributes
            }

            $parts = explode('\\', $className);
            $attributeNames[] = end($parts);
        }

        return $attributeNames;
    }

    /**
     * Validate semantic variable with parameter attributes for hierarchical validation
     *
     * @param string              $variableName        Variable name for basic semantic validation
     * @param ParameterAttributes $parameterAttributes Parameter attributes for hierarchical validation
     * @param mixed               ...$args             Arguments to validate
     */
    public function validateWithAttributes(string $variableName, array $parameterAttributes = [], mixed ...$args): Errors
    {
        $semanticClass = $this->resolveSemanticClass($variableName);

        if ($semanticClass === null) {
            // No semantic class found - return no errors (opt-in validation)
            return new NullErrors();
        }

        $validationMethods = $this->getMatchingValidationMethods($semanticClass, $parameterAttributes, $args);

        if (empty($validationMethods)) {
            // No matching validation methods found
            return new NullErrors();
        }

        $exceptions = [];

        foreach ($validationMethods as $method) {
            try {
                $methodArgs = $this->resolveMethodArguments($method, $args);
                $method->invoke($semanticClass, ...$methodArgs);
            } catch (DomainException $exception) {
                $exceptions[] = $exception;
            }
        }

        return empty($exceptions) ? new NullErrors() : new Errors($exceptions);
    }

    /**
     * Resolve semantic class from variable name
     */
    private function resolveSemanticClass(string $variableName): object|null
    {
        $className = $this->convertToClassName($variableName);
        $fullClassName = "{$this->semanticNamespace}\\$className";

        if (! class_exists($fullClassName)) {
            trigger_error("Semantic variable '{$className}' not registered in ontology namespace {$this->semanticNamespace}", E_USER_NOTICE);

            return null;
        }

        /** @psalm-suppress MixedMethodCall */
        return new $fullClassName();
    }

    /**
     * Convert variable name to class name
     * Examples: email -> Email, user_id -> UserId, zip_code -> ZipCode
     */
    private function convertToClassName(string $variableName): string
    {
        // Convert snake_case to PascalCase
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $variableName)));
    }

    /**
     * Get validation methods with #[Validate] attribute that match the given arguments
     *
     * @param object              $semanticClass       The semantic validation class
     * @param ParameterAttributes $parameterAttributes Parameter attributes for filtering
     * @param ValidationArguments $validationArgs      Arguments to validate
     *
     * @return ReflectionMethods
     * @phpstan-return array<int, ReflectionMethod>
     */
    private function getMatchingValidationMethods(object $semanticClass, array $parameterAttributes, array $validationArgs): array
    {
        $reflection = new ReflectionClass($semanticClass);
        $methodsByName = [];

        // Simple method matching: check each validation method's parameters
        foreach ($reflection->getMethods() as $method) {
            if (empty($method->getAttributes(Validate::class))) {
                continue;
            }

            // Check if this method matches our input parameters
            $methodParameters = $method->getParameters();
            $matches = true;

            $nonInjectParams = array_filter($methodParameters, static fn ($p) => empty($p->getAttributes(Inject::class)));

            // Check if we have enough arguments for this method
            if (count($validationArgs) < count($nonInjectParams)) {
                continue;
            }

            foreach ($methodParameters as $methodParam) {
                if ($methodParam->getAttributes(Inject::class)) {
                    continue; // Skip injected parameters
                }

                // Check if parameter attributes match (if method param has semantic attributes)
                $methodParamAttrs = $this->extractAttributeNames($methodParam);
                if (! empty($methodParamAttrs)) {
                    // Method parameter has attributes - input must have matching attributes
                    $hasMatchingAttr = false;
                    foreach ($methodParamAttrs as $attr) {
                        if (in_array($attr, $parameterAttributes, true)) {
                            $hasMatchingAttr = true;
                            break;
                        }
                    }

                    if (! $hasMatchingAttr) {
                        $matches = false;
                        break;
                    }
                }
            }

            if ($matches) {
                $methodsByName[$method->getName()] = $method;
            }
        }

        return array_values($methodsByName);
    }

    /**
     * Check if a class is marked with SemanticTag attribute
     *
     * @phpstan-ignore method.unused
     */
    private function isSemanticTagClass(string $className): bool
    {
        if (! class_exists($className)) {
            return false;
        }

        $reflection = new ReflectionClass($className);

        return ! empty($reflection->getAttributes(SemanticTag::class));
    }

    /**
     * Resolve method arguments by extracting only the arguments needed for this method
     *
     * @param ValidationArguments $inputArgs
     *
     * @return ValidationArguments
     */
    private function resolveMethodArguments(ReflectionMethod $method, array $inputArgs): array
    {
        $resolvedArgs = [];
        $paramIndex = 0;

        foreach ($method->getParameters() as $param) {
            // Skip injected parameters - they will be handled by DI
            if (! empty($param->getAttributes(Inject::class))) {
                continue;
            }

            // Take arguments in order for non-injected parameters
            if ($paramIndex < count($inputArgs)) {
                /** @psalm-suppress MixedAssignment */
                $resolvedArgs[] = $inputArgs[$paramIndex];
            }

            $paramIndex++;
        }

        return $resolvedArgs;
    }

    /**
     * Legacy method: Validate semantic variable with given arguments (for backward compatibility)
     */
    public function validate(string $variableName, mixed ...$args): Errors
    {
        return $this->validateWithAttributes($variableName, [], ...$args);
    }

    /**
     * Legacy method: Validate semantic variable with given arguments
     */
    public function validateLegacy(string $variableName, mixed ...$args): Errors
    {
        return $this->validateWithAttributes($variableName, [], ...$args);
    }

    /**
     * Legacy method: Validate all semantic variables in an object
     */
    public function validateObject(object $object): Errors
    {
        $reflection = new ReflectionClass($object);
        $allErrors = [];

        foreach ($reflection->getProperties() as $property) {
            $property->setAccessible(true);
            /** @var mixed $value */
            $value = $property->getValue($object);
            $propertyName = $property->getName();

            $errors = $this->validateLegacy($propertyName, $value);
            if ($errors->hasErrors()) {
                $allErrors = [...$allErrors, ...$errors->exceptions];
            }
        }

        return empty($allErrors) ? new NullErrors() : new Errors($allErrors);
    }

    /**
     * Legacy method: Validate semantic variables and throw exception if errors found
     */
    public function validateAndThrow(string $variableName, mixed ...$args): void
    {
        $errors = $this->validateLegacy($variableName, ...$args);

        if ($errors->hasErrors()) {
            throw new SemanticVariableException($errors);
        }
    }
}
