<?php

declare(strict_types=1);

namespace Be\Framework\SemanticVariable;

use Be\Framework\Attribute\Validate;
use Be\Framework\Exception\SemanticVariableException;
use Be\Framework\Types;
use DomainException;
use Override;
use Ray\Di\Di\Named;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;

use function array_key_exists;
use function class_exists;
use function get_object_vars;
use function in_array;
use function is_array;
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
    /** @var array<string, class-string> */
    private readonly array $classMap;
    private readonly SemanticValidationMethodResolver $validationMethodResolver;

    /** @param array<string, class-string>|SemanticValidationMethodResolver|null $classMapOrValidationMethodResolver */
    public function __construct(
        #[Named('semantic_namespace')]
        private readonly string $semanticNamespace,
        array|SemanticValidationMethodResolver|null $classMapOrValidationMethodResolver = null,
        SemanticValidationMethodResolver|null $validationMethodResolver = null,
    ) {
        if ($classMapOrValidationMethodResolver instanceof SemanticValidationMethodResolver) {
            $this->classMap = [];
            $this->validationMethodResolver = $classMapOrValidationMethodResolver;

            return;
        }

        $this->classMap = is_array($classMapOrValidationMethodResolver) ? $classMapOrValidationMethodResolver : [];
        $this->validationMethodResolver = $validationMethodResolver ?? new SemanticValidationMethodResolver();
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
            if ($this->validationMethodResolver->hasInjectAttribute($parameter)) {
                continue;
            }

            $paramName = $parameter->getName();

            // Skip if object doesn't have corresponding property
            if (! array_key_exists($paramName, $objectProperties)) {
                continue;
            }

            /** @psalm-suppress MixedAssignment */
            $propertyValue = $objectProperties[$paramName];
            $parameterAttributes = $this->validationMethodResolver->extractAttributeNames($parameter);

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
     * @phpstan-param array<string, mixed> $args
     *
     * @return Errors Validation errors (empty if validation passes)
     */
    #[Override]
    public function validateArgs(ReflectionMethod $method, array $args): Errors
    {
        $allErrors = [];

        // First pass: single-field validation
        foreach ($method->getParameters() as $parameter) {
            // Skip #[Inject] parameters
            if ($this->validationMethodResolver->hasInjectAttribute($parameter)) {
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

        // Second pass: cross-field validation for multi-parameter #[Validate] methods
        $crossFieldErrors = $this->validateCrossFieldArgs($method, $args);
        $allErrors = [...$allErrors, ...$crossFieldErrors];

        return empty($allErrors) ? new NullErrors() : new Errors($allErrors);
    }

    /**
     * Validate cross-field constraints using multi-parameter #[Validate] methods
     *
     * For each semantic class resolved from constructor parameters, finds #[Validate]
     * methods with 2+ non-#[Inject] parameters whose names ALL exist in $allArgs.
     *
     * @param ReflectionMethod     $method  Constructor with parameter definitions
     * @param ConstructorArguments $allArgs All constructor argument values
     * @phpstan-param array<string, mixed> $allArgs
     *
     * @return list<DomainException>
     */
    private function validateCrossFieldArgs(ReflectionMethod $method, array $allArgs): array
    {
        $exceptions = [];
        $checkedClasses = [];

        foreach ($method->getParameters() as $parameter) {
            if ($this->validationMethodResolver->hasInjectAttribute($parameter)) {
                continue;
            }

            $semanticClass = $this->resolveSemanticClass($parameter->getName());
            if ($semanticClass === null) {
                continue;
            }

            $className = $semanticClass::class;
            if (in_array($className, $checkedClasses, true)) {
                continue;
            }

            $checkedClasses[] = $className;
            $exceptions = [...$exceptions, ...$this->invokeCrossFieldMethods($semanticClass, $allArgs)];
        }

        return $exceptions;
    }

    /**
     * Invoke multi-parameter #[Validate] methods whose parameter names match available args
     *
     * @param ConstructorArguments $allArgs All constructor argument values
     * @phpstan-param array<string, mixed> $allArgs
     *
     * @return list<DomainException>
     */
    private function invokeCrossFieldMethods(object $semanticClass, array $allArgs): array
    {
        $exceptions = [];
        $reflection = new ReflectionClass($semanticClass);

        foreach ($reflection->getMethods() as $validateMethod) {
            if (empty($validateMethod->getAttributes(Validate::class))) {
                continue;
            }

            $methodArgs = $this->validationMethodResolver->matchArgsByName($validateMethod, $allArgs);
            if ($methodArgs === null) {
                continue;
            }

            try {
                $validateMethod->invoke($semanticClass, ...$methodArgs);
            } catch (DomainException $exception) {
                $exceptions[] = $exception;
            }
        }

        return $exceptions;
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
        $attributes = $this->validationMethodResolver->extractAttributeNames($parameter);

        return $this->validateWithAttributes($variableName, $attributes, $value);
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

        $validationMethods = $this->validationMethodResolver->getMatchingValidationMethods($semanticClass, $parameterAttributes, $args);

        if (empty($validationMethods)) {
            // No matching validation methods found
            return new NullErrors();
        }

        $exceptions = [];

        foreach ($validationMethods as $method) {
            try {
                $methodArgs = $this->validationMethodResolver->resolveMethodArguments($method, $args);
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
        $fullClassName = $this->classMap[$className] ?? "{$this->semanticNamespace}\\$className";

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
     * Legacy method: Validate semantic variable with given arguments (for backward compatibility)
     *
     * @deprecated Use validateArgs() or validateArg() instead
     */
    public function validate(string $variableName, mixed ...$args): Errors
    {
        return $this->validateWithAttributes($variableName, [], ...$args);
    }

    /**
     * Legacy method: Validate semantic variable with given arguments
     *
     * @deprecated Use validateArgs() or validateArg() instead
     */
    public function validateLegacy(string $variableName, mixed ...$args): Errors
    {
        return $this->validateWithAttributes($variableName, [], ...$args);
    }

    /**
     * Legacy method: Validate all semantic variables in an object
     *
     * @deprecated Use validateProps() instead
     */
    public function validateObject(object $object): Errors
    {
        $reflection = new ReflectionClass($object);
        $allErrors = [];

        foreach ($reflection->getProperties() as $property) {
            /** @var mixed $value */
            $value = $property->getValue($object);
            $propertyName = $property->getName();

            $errors = $this->validateWithAttributes($propertyName, [], $value);
            if ($errors->hasErrors()) {
                $allErrors = [...$allErrors, ...$errors->exceptions];
            }
        }

        return empty($allErrors) ? new NullErrors() : new Errors($allErrors);
    }

    /**
     * Legacy method: Validate semantic variables and throw exception if errors found
     *
     * @deprecated Use validateArgs() and check hasErrors() instead
     */
    public function validateAndThrow(string $variableName, mixed ...$args): void
    {
        $errors = $this->validateWithAttributes($variableName, [], ...$args);

        if ($errors->hasErrors()) {
            throw new SemanticVariableException($errors);
        }
    }
}
