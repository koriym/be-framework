<?php

declare(strict_types=1);

namespace Be\Framework;

use Ray\Di\Di\Inject;
use Ray\Di\InjectorInterface;
use Be\Framework\Debug\DebugLoggerInterface;
use Be\Framework\Debug\EchoDebugLogger;
use Ray\InputQuery\Attribute\Input;
use ReflectionClass;
use ReflectionParameter;

use function get_object_vars;

/**
 * Debug version of BecomingArguments with verbose logging
 *
 * Uses composition to wrap BecomingArguments and add debug logging
 * without modifying the original class or requiring inheritance.
 */
final class DebugBecomingArguments implements BecomingArgumentsInterface
{
    public function __construct(
        private BecomingArguments $becomingArguments,
        private DebugLoggerInterface $logger = new EchoDebugLogger(),
    ) {
    }

    public function __invoke(object $current, string $becoming): array
    {
        $this->logger->debug("\n=== DebugBecomingArguments ===");
        $this->logger->debug('Current object: ' . $current::class);
        $this->logger->debug('Becoming: ' . $becoming);

        $properties = get_object_vars($current);
        $this->logger->dump('Available properties', $properties);

        $targetClass = new ReflectionClass($becoming);
        $constructor = $targetClass->getConstructor();

        if ($constructor === null) {
            $this->logger->debug('No constructor found');

            return [];
        }

        $this->logger->debug("\nProcessing constructor parameters:");

        // Log parameter details before processing
        foreach ($constructor->getParameters() as $param) {
            $this->logParameterDetails($param, $properties);
        }

        // Delegate to the original BecomingArguments for actual processing
        $args = $this->becomingArguments->__invoke($current, $becoming);

        $this->logger->dump('Final resolved args', $args);
        $this->logger->debug('=== End Debug ===\n');

        return $args;
    }

    private function logParameterDetails(ReflectionParameter $param, array $properties): void
    {
        $this->logger->debug('- Parameter: ' . $param->getName());

        $hasInput = ! empty($param->getAttributes(Input::class));
        $hasInject = ! empty($param->getAttributes(Inject::class));

        $this->logger->debug('  #[Input]: ' . ($hasInput ? 'Yes' : 'No'));
        $this->logger->debug('  #[Inject]: ' . ($hasInject ? 'Yes' : 'No'));

        if ($hasInput) {
            $paramName = $param->getName();
            if (isset($properties[$paramName])) {
                $this->logger->dump('  Available in properties', $properties[$paramName]);
            } elseif ($param->isDefaultValueAvailable()) {
                $this->logger->dump('  Has default value', $param->getDefaultValue());
            } else {
                $this->logger->debug('  ERROR: Required parameter missing!');
            }
        } else {
            $this->logger->debug('  Will be resolved from DI container');
        }
    }
}
