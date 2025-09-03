<?php

declare(strict_types=1);

namespace Be\Framework;

use Be\Framework\Attribute\Validate;
use DomainException;
use ReflectionClass;
use ReflectionMethod;

use function ucfirst;
use function str_replace;
use function ucwords;
use function class_exists;

/**
 * Validates semantic variables based on their names
 * 
 * Automatically resolves validation classes from variable names and 
 * executes appropriate validation methods based on argument patterns.
 */
final class SemanticValidator
{
    public function __construct(
        private BecomingArgumentsInterface $becomingArguments
    ) {}
    
    /**
     * Validate semantic variable with given arguments
     */
    public function validate(string $variableName, mixed ...$args): Errors
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
    private function resolveSemanticClass(string $variableName): ?object
    {
        $className = $this->convertToClassName($variableName);
        $fullClassName = "SemanticVariables\\{$className}";
        
        if (!class_exists($fullClassName)) {
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
     * Get validation methods that match the given arguments
     * 
     * @return array<ReflectionMethod>
     */
    private function getMatchingValidationMethods(object $semanticClass, array $args): array
    {
        $reflection = new ReflectionClass($semanticClass);
        $methods = [];
        
        foreach ($reflection->getMethods() as $method) {
            if (!empty($method->getAttributes(Validate::class))) {
                if ($this->methodMatchesArguments($method, $args)) {
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
            if (empty($param->getAttributes(\Ray\Di\Di\Inject::class))) {
                $methodArgCount++;
            }
        }
        
        return $methodArgCount === $inputArgCount;
    }
    
    /**
     * Resolve method arguments using BecomingArguments
     */
    private function resolveMethodArguments(ReflectionMethod $method, array $inputArgs): array
    {
        // Create a temporary object to hold input arguments
        $inputObject = new class($inputArgs) {
            public function __construct(private array $args) {}
            public function getArgs(): array { return $this->args; }
        };
        
        // Use BecomingArguments to resolve method parameters
        return ($this->becomingArguments)($inputObject, $method->getName());
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
            throw new ValidationException($errors);
        }
    }
}