<?php

declare(strict_types=1);

namespace Be\Framework\SemanticVariable;

use ReflectionMethod;

/**
 * Collection of SemanticParam objects for a method's parameters
 *
 * Provides unified parameter validation and management for methods
 * with semantic parameter attributes. Completely cacheable and minimal!
 */
final class SemanticParams
{
    /** @var array<string, SemanticParam> */
    private array $params = [];

    public function __construct(
        private ReflectionMethod $method,
        private SemanticValidator $validator,
    ) {
        foreach ($method->getParameters() as $parameter) {
            $this->params[$parameter->getName()] = new SemanticParam($parameter, $validator);
        }
    }

    /**
     * Validate all parameters with given values
     */
    public function validate(array $values): Errors
    {
        $allErrors = [];

        foreach ($this->params as $name => $param) {
            if (isset($values[$name])) {
                $errors = $param->validate($values[$name]);
                if ($errors->hasErrors()) {
                    $allErrors = [...$allErrors, ...$errors->exceptions];
                }
            }
        }

        return empty($allErrors) ? new NullErrors() : new Errors($allErrors);
    }
}
