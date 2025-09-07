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
use Ray\InputQuery\Attribute\Input;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;

use function array_key_exists;
use function array_values;
use function class_exists;
use function count;
use function end;
use function error_log;
use function explode;
use function get_object_vars;
use function in_array;
use function str_replace;
use function str_starts_with;
use function strtolower;
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
        private string $ontlogyNamespace,
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

        $validationMethods = $this->getMatchingValidationMethods($semanticClass, $args);

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
        $fullClassName = "{$this->ontlogyNamespace}\\$className";

        if (! class_exists($fullClassName)) {
            error_log("Semantic variable '{$className}' not registered in ontology namespace {$this->ontlogyNamespace}", E_USER_NOTICE);

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
     * @param object              $semanticClass  The semantic validation class
     * @param ValidationArguments $validationArgs Arguments to validate
     *
     * @return ReflectionMethods
     * @phpstan-return array<int, ReflectionMethod>
     */
    private function getMatchingValidationMethods(object $semanticClass, array $validationArgs): array
    {
        $reflection = new ReflectionClass($semanticClass);
        $methodsByName = [];

        // Collect all methods with #[Validate] attribute
        foreach ($reflection->getMethods() as $method) {
            if (! empty($method->getAttributes(Validate::class)) && $this->methodMatchesArguments($method, $validationArgs)) {
                $methodsByName[$method->getName()] = $method;
            }
        }

        return array_values($methodsByName);
    }

    /**
     * Check if method signature matches the provided arguments
     *
     * @param ValidationArguments $args
     */
    private function methodMatchesArguments(ReflectionMethod $method, array $args): bool
    {
        $parameters = $method->getParameters();
        $inputArgCount = count($args);
        $methodArgCount = 0;

        // Count non-injected parameters
        foreach ($parameters as $param) {
            if (empty($param->getAttributes(Inject::class))) {
                $methodArgCount++;
            }
        }

        return $methodArgCount === $inputArgCount;
    }

    /**
     * Check if the method is attribute-specific (method parameters have matching SemanticTag attributes)
     *
     * @param ParameterAttributes $inputParameterAttributes
     */
    private function isAttributeSpecificMethod(ReflectionMethod $method, array $inputParameterAttributes): bool
    {
        if (empty($inputParameterAttributes)) {
            return false;
        }

        // Check if method has parameters with matching SemanticTag attributes
        foreach ($method->getParameters() as $methodParam) {
            foreach ($methodParam->getAttributes() as $attr) {
                // Get the attribute class name (e.g., "Be\Framework\SemanticTag\Adult")
                $attrClassName = $attr->getName();

                // Extract tag name from class name (e.g., "Adult")
                $parts = explode('\\', $attrClassName);
                $tagName = end($parts);

                // Check if this tag matches input attributes and is a SemanticTag
                if (
                    in_array($tagName, $inputParameterAttributes, true) &&
                    $this->isSemanticTagClass($attrClassName)
                ) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if a class is marked with SemanticTag attribute
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
     * Check if the method is a base validation method (e.g., validateAge for basic age)
     */
    private function isBaseValidationMethod(ReflectionMethod $method, string $variableName): bool
    {
        $methodName = strtolower($method->getName());
        $className = strtolower($this->convertToClassName($variableName));

        // Base methods follow pattern: validate + VariableName (e.g., validateAge)
        // or validate + VariableName + additional descriptors (e.g., validateEmailConfirmation)
        return str_starts_with($methodName, 'validate' . $className);
    }

    /**
     * Resolve method arguments - simply return input args for now
     *
     * @param ValidationArguments $inputArgs
     *
     * @return ValidationArguments
     */
    private function resolveMethodArguments(ReflectionMethod $method, array $inputArgs): array
    {
        // For now, just return the input arguments as-is
        // TODO: Implement proper argument resolution if needed
        return $inputArgs;
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
