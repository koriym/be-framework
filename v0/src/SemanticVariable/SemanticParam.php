<?php

declare(strict_types=1);

namespace Be\Framework\SemanticVariable;

use ReflectionParameter;

use function basename;
use function str_replace;

final class SemanticParam
{
    public readonly string $name;
    public readonly array $attributeNames;

    public function __construct(
        private ReflectionParameter $parameter,
        private SemanticValidator $validator,
    ) {
        $this->name = $parameter->getName();
        $this->attributeNames = $this->extractAttributeNames($parameter);
    }

    public function validate(mixed $value): Errors
    {
        return $this->validator->validateWithAttributes(
            $this->name,
            $this->attributeNames,
            $value,
        );
    }

    /**
     * Extract attribute names from ReflectionParameter
     */
    private function extractAttributeNames(ReflectionParameter $parameter): array
    {
        $attributeNames = [];
        foreach ($parameter->getAttributes() as $attribute) {
            $className = $attribute->getName();
            // Extract just the class name (e.g., Adult from Be\Framework\SemanticTag\Adult)
            $attributeNames[] = basename(str_replace('\\', '/', $className));
        }

        return $attributeNames;
    }
}
