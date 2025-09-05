<?php

declare(strict_types=1);

namespace Be\Framework;

use Be\Framework\Exception\ConflictingParameterAttributes;
use Be\Framework\Exception\MissingParameterAttribute;
use Be\Framework\Exception\SemanticVariableException;
use Be\Framework\SemanticVariable\SemanticValidatorInterface;
use Override;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use Ray\Di\InjectorInterface;
use Ray\InputQuery\Attribute\Input;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;

use function get_object_vars;

/**
 * Resolves constructor arguments
 *
 * for metamorphosis transformations
 *
 * Implements Be Framework's philosophy of explicit dependency declaration:
 * - All constructor parameters must have either #[Input] or #[Inject] attributes
 * - #[Input] parameters are resolved from the current object's properties
 * - #[Inject] parameters are resolved from the DI container
 * - Object properties are preserved as-is (no flattening)
 * - Supports #[Named] for DI resolution
 */
final class BecomingArguments implements BecomingArgumentsInterface
{
    public function __construct(
        private InjectorInterface $injector,
        private SemanticValidatorInterface $semanticValidator,
    ) {
    }

    #[Override]
    public function be(object $current, string $becoming): array
    {
        $properties = get_object_vars($current);
        /** @var class-string $becoming */
        $targetClass = new ReflectionClass($becoming);
        $constructor = $targetClass->getConstructor();

        if ($constructor === null) {
            return [];
        }

        /** @var array<string, mixed> $args */
        $args = [];
        foreach ($constructor->getParameters() as $param) {
            $paramName = $param->getName();
            $isInput = $this->isInputParameter($param);
            if ($isInput) {
                /** @var mixed $value */
                $value = $properties[$paramName];
                /** @psalm-suppress MixedAssignment */
                $args[$paramName] = $value;      // #[Input]
                continue;
            }

            /** @var mixed $value */
            $value = $this->getInjectParameter($param);
            /** @psalm-suppress MixedAssignment */
            $args[$paramName] = $value; // #[Inject]
        }

        $errors = $this->semanticValidator->validateArgs($constructor, $args);

        if ($errors->hasErrors()) {
            throw new SemanticVariableException($errors);
        }

        return $args;
    }

    /**
     * Resolves #[Inject] parameters from DI container
     *
     * Supports #[Named] attributes for named bindings.
     * Scalar types require #[Named] or default values (Ray.Di historical compatibility).
     */
    private function getInjectParameter(ReflectionParameter $param): mixed
    {
        $namedAttributes = $param->getAttributes(Named::class);
        $named = ! empty($namedAttributes) ? $namedAttributes[0]->newInstance()->value : '';

        $type = $param->getType();
        $interface = $type instanceof ReflectionNamedType && ! $type->isBuiltin() ? $type->getName() : '';

        /** @var class-string $interface */
        return $this->injector->getInstance($interface, $named);
    }

    /**
     * Returns if parameter is #[Input] (validates attributes as side effect)
     *
     * Returns true for #[Input], false for #[Inject]
     * Enforces Be Framework's philosophy: "Describe Yourself (Well)"
     * All dependencies must be explicitly declared for clarity and safety.
     */
    private function isInputParameter(ReflectionParameter $param): bool
    {
        $inputAttributes = $param->getAttributes(Input::class);
        $injectAttributes = $param->getAttributes(Inject::class);

        $hasInput = ! empty($inputAttributes);
        $hasInject = ! empty($injectAttributes);

        if ($hasInput && $hasInject) {
            throw ConflictingParameterAttributes::create($param);
        }

        if (! $hasInput && ! $hasInject) {
            throw MissingParameterAttribute::create($param);
        }

        return $hasInput;
    }
}
