<?php

declare(strict_types=1);

namespace Be\Framework\SemanticVariable;

use Be\Framework\Attribute\Validate;
use Be\Framework\BecomingArgumentsInterface;
use DomainException;
use Ray\Di\Di\Inject;
use ReflectionClass;
use ReflectionMethod;

use function class_exists;
use function count;
use function str_replace;
use function str_starts_with;
use function strtolower;
use function ucwords;

/**
 * Validates semantic variables based on their names
 *
 * Automatically resolves validation classes from variable names and
 * executes appropriate validation methods based on argument patterns.
 */
final class SemanticValidator
{
    public function __construct(
        private BecomingArgumentsInterface $becomingArguments,
        private string $semanticNamespace = 'Be\\App\\SemanticVariable',
    ) {
    }

    /**
     * Validate semantic variable with given arguments
     */
    public function validate(string $variableName, mixed ...$args): Errors
    {
        return $this->validateWithAttributes($variableName, [], ...$args);
    }

    /**
     * Validate semantic variable with parameter attributes for hierarchical validation
     *
     * @param string $variableName        Variable name for basic semantic validation
     * @param array  $parameterAttributes Parameter attributes for hierarchical validation
     * @param mixed  ...$args             Arguments to validate
     */
    public function validateWithAttributes(string $variableName, array $parameterAttributes = [], mixed ...$args): Errors
    {
        $semanticClass = $this->resolveSemanticClass($variableName);

        if ($semanticClass === null) {
            // No semantic class found - return no errors (opt-in validation)
            return new NullErrors();
        }

        $validationMethods = $this->getMatchingValidationMethods($semanticClass, $variableName, $parameterAttributes, $args);

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
            return null;
        }

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
     * Get validation methods that match the given arguments and parameter attributes
     *
     * @param object $semanticClass       The semantic validation class
     * @param string $variableName        The variable name for base validation
     * @param array  $parameterAttributes Parameter attributes for hierarchical validation
     * @param array  $validationArgs      Arguments to validate
     *
     * @return array<ReflectionMethod>
     */
    private function getMatchingValidationMethods(object $semanticClass, string $variableName, array $parameterAttributes, array $validationArgs): array
    {
        $reflection = new ReflectionClass($semanticClass);
        $methods = [];

        // First, always add the base validation method if it exists and matches
        foreach ($reflection->getMethods() as $method) {
            if (! empty($method->getAttributes(Validate::class)) && $this->methodMatchesArguments($method, $validationArgs)) {
                // Check if this is an attribute-specific method
                if ($this->isAttributeSpecificMethod($method, $parameterAttributes)) {
                    $methods[] = $method;
                } elseif (empty($parameterAttributes) && $this->isBaseValidationMethod($method, $variableName)) {
                    // Use base validation when no specific attributes are present
                    $methods[] = $method;
                }
            }
        }

        return $methods;
    }

    /**
     * Check if method signature matches the provided arguments
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
     * Check if the method is attribute-specific (e.g., validateTeen for #[Teen])
     */
    private function isAttributeSpecificMethod(ReflectionMethod $method, array $parameterAttributes): bool
    {
        if (empty($parameterAttributes)) {
            return false;
        }

        $methodName = strtolower($method->getName());

        // Check if method name matches any parameter attribute
        foreach ($parameterAttributes as $attributeName) {
            $expectedMethodName = 'validate' . strtolower($attributeName);
            if ($methodName === $expectedMethodName) {
                return true;
            }
        }

        return false;
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
     */
    private function resolveMethodArguments(ReflectionMethod $method, array $inputArgs): array
    {
        // For now, just return the input arguments as-is
        // TODO: Implement proper argument resolution if needed
        return $inputArgs;
    }

    /**
     * Validate all semantic variables in an object
     */
    public function validateObject(object $object): Errors
    {
        $reflection = new ReflectionClass($object);
        $allErrors = [];

        foreach ($reflection->getProperties() as $property) {
            $property->setAccessible(true);
            $value = $property->getValue($object);
            $propertyName = $property->getName();

            $errors = $this->validate($propertyName, $value);
            if ($errors->hasErrors()) {
                $allErrors = [...$allErrors, ...$errors->exceptions];
            }
        }

        return empty($allErrors) ? new NullErrors() : new Errors($allErrors);
    }

    /**
     * Validate semantic variables and throw exception if errors found
     */
    public function validateAndThrow(string $variableName, mixed ...$args): void
    {
        $errors = $this->validate($variableName, ...$args);

        if ($errors->hasErrors()) {
            throw new DomainException('Validation failed');
        }
    }
}
