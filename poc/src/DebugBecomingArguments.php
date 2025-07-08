<?php

declare(strict_types=1);

namespace Ray\Framework;

use InvalidArgumentException;
use Ray\Di\Di\Inject;
use Ray\InputQuery\Attribute\Input;
use ReflectionClass;
use ReflectionParameter;

use function get_object_vars;
use function sprintf;
use function var_dump;

/**
 * Debug version of BecomingArguments with verbose logging
 */
final class DebugBecomingArguments implements BecomingArgumentsInterface
{
    public function __invoke(object $current, string $becoming): array
    {
        echo "\n=== DebugBecomingArguments ===\n";
        echo 'Current object: ' . $current::class . "\n";
        echo 'Becoming: ' . $becoming . "\n";

        $properties = get_object_vars($current);
        echo "Available properties:\n";
        var_dump($properties);

        $targetClass = new ReflectionClass($becoming);
        $constructor = $targetClass->getConstructor();

        if ($constructor === null) {
            echo "No constructor found\n";

            return [];
        }

        $args = [];
        echo "\nProcessing constructor parameters:\n";

        foreach ($constructor->getParameters() as $param) {
            echo '- Parameter: ' . $param->getName() . "\n";

            $inputAttrs = $param->getAttributes(Input::class);
            $injectAttrs = $param->getAttributes(Inject::class);

            echo '  #[Input]: ' . (empty($inputAttrs) ? 'No' : 'Yes') . "\n";
            echo '  #[Inject]: ' . (empty($injectAttrs) ? 'No' : 'Yes') . "\n";

            $this->validateParameterAttributes($param);

            if (! empty($inputAttrs)) {
                $paramName = $param->getName();

                if (isset($properties[$paramName])) {
                    $value = $properties[$paramName];
                    echo '  Resolved from properties: ';
                    var_dump($value);
                    $args[$paramName] = $value;
                } elseif ($param->isDefaultValueAvailable()) {
                    $value = $param->getDefaultValue();
                    echo '  Using default value: ';
                    var_dump($value);
                    $args[$paramName] = $value;
                } else {
                    echo "  ERROR: Required parameter missing!\n";

                    throw new InvalidArgumentException(
                        sprintf(
                            'Required #[Input] parameter "%s" is missing from object properties in %s::%s',
                            $paramName,
                            $becoming,
                            $constructor->getName(),
                        ),
                    );
                }
            } else {
                echo "  Skipping (for DI container)\n";
            }
        }

        echo "\nFinal resolved args:\n";
        var_dump($args);
        echo "=== End Debug ===\n\n";

        return $args;
    }

    private function validateParameterAttributes(ReflectionParameter $param): void
    {
        $hasInput = ! empty($param->getAttributes(Input::class));
        $hasInject = ! empty($param->getAttributes(Inject::class));

        if (! $hasInput && ! $hasInject) {
            throw new InvalidArgumentException(
                sprintf(
                    'Parameter "%s" in %s::%s must have either #[Input] or #[Inject] attribute. ' .
                    'Ray.Framework requires explicit dependency declaration for safety and clarity. ' .
                    'Use #[Input] if this should come from the previous object, ' .
                    'or #[Inject] if this should come from the DI container.',
                    $param->getName(),
                    $param->getDeclaringClass()->getName(),
                    $param->getDeclaringFunction()->getName(),
                ),
            );
        }
    }
}
