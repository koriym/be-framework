<?php

declare(strict_types=1);

namespace Be\Framework\SemanticLog;

use Be\Framework\BecomingArgumentsInterface;
use Be\Framework\Being;
use Be\Framework\SemanticLog\Context\DestinationNotFound;
use Be\Framework\SemanticLog\Context\FinalDestination;
use Be\Framework\SemanticLog\Context\MetamorphosisCloseContext;
use Be\Framework\SemanticLog\Context\MetamorphosisOpenContext;
use Be\Framework\SemanticLog\Context\MultipleDestination;
use Be\Framework\SemanticLog\Context\SingleDestination;
use Koriym\SemanticLogger\SemanticLoggerInterface;
use Ray\Di\Di\Inject;
use ReflectionClass;

use function array_key_exists;
use function array_map;
use function get_object_vars;
use function gettype;
use function implode;
use function is_object;
use function is_string;

/**
 * Be Framework Logger
 *
 * Handles all semantic logging concerns, keeping Becoming engine clean.
 */
final class Logger implements LoggerInterface
{
    private Being $being;

    public function __construct(
        private SemanticLoggerInterface $logger,
        private BecomingArgumentsInterface $becomingArguments,
    ) {
        $this->being = new Being($this, $this->becomingArguments);
    }

    /**
     * Log transformation start
     */
    public function open(object $current, string|array $becoming): string
    {
        $fromClass = $current::class;

        if (is_string($becoming)) {
            // Single transformation case
            $beAttribute = "#[Be({$becoming}::class)]";
            $args = $this->becomingArguments->be($current, $becoming);
            $immanentSources = $this->extractImmanentSources($current, $args);
            $transcendentSources = $this->extractTranscendentSources($args, $becoming);

            return $this->logger->open(new MetamorphosisOpenContext(
                fromClass: $fromClass,
                beAttribute: $beAttribute,
                immanentSources: $immanentSources,
                transcendentSources: $transcendentSources,
            ));
        }

        // Array transformation case - log the attempt with all candidate classes
        $classNames = implode(', ', array_map(static fn ($class) => $class . '::class', $becoming));
        $beAttribute = "#[Be([{$classNames}])]";

        return $this->logger->open(new MetamorphosisOpenContext(
            fromClass: $fromClass,
            beAttribute: $beAttribute,
            immanentSources: [],
            transcendentSources: [],
        ));
    }

    /**
     * Log transformation completion
     */
    public function close(object|null $result, string $openId, string|null $error = null): void
    {
        // Skip if no open ID
        if ($openId === '') {
            return;
        }

        if ($result === null) {
            // Error case
            $this->logger->close(new MetamorphosisCloseContext(
                properties: [],
                be: new DestinationNotFound(
                    error: $error ?? 'Unknown error',
                    attemptedClasses: [],
                ),
            ), $openId);

            return;
        }

        // Success case
        $properties = $this->extractProperties($result);
        $destination = $this->determineDestination($result);

        $this->logger->close(new MetamorphosisCloseContext(
            properties: $properties,
            be: $destination,
        ), $openId);
    }

    private function extractImmanentSources(object $current, array $args): array
    {
        $immanentSources = [];
        $properties = get_object_vars($current);

        // Use parameter names for reliable mapping (BecomingArguments ensures parameter names match property names for #[Input])
        foreach ($args as $paramName => $value) {
            if (array_key_exists($paramName, $properties)) {
                $immanentSources[$paramName] = $current::class . '::' . $paramName;
            }
        }

        return $immanentSources;
    }

    private function extractTranscendentSources(array $args, string $becoming): array
    {
        $transcendentSources = [];
        $reflectionClass = new ReflectionClass($becoming);
        $constructor = $reflectionClass->getConstructor();

        if ($constructor === null) {
            return $transcendentSources;
        }

        // Check each parameter for #[Inject] attribute
        foreach ($constructor->getParameters() as $param) {
            $paramName = $param->getName();

            if (! array_key_exists($paramName, $args)) {
                continue;
            }

            $hasInject = ! empty($param->getAttributes(Inject::class));
            if ($hasInject) {
                $value = $args[$paramName];

                // For objects, use their class name; for scalars, use their type/value representation
                if (is_object($value)) {
                    $transcendentSources[$paramName] = $value::class;
                } else {
                    // For scalar/other types, show the type information
                    $transcendentSources[$paramName] = gettype($value) . ':' . (string) $value;
                }
            }
        }

        return $transcendentSources;
    }

    private function extractProperties(object $result): array
    {
        // @todo Handle uninitialized properties in Accept pattern objects
        // For now, get_object_vars() covers all realistic Be Framework objects
        return get_object_vars($result);
    }

    private function determineDestination(object $result): SingleDestination|MultipleDestination|FinalDestination|DestinationNotFound
    {
        $nextBecoming = $this->being->willBe($result);

        if ($nextBecoming === null) {
            return new FinalDestination($result::class);
        }

        if (is_string($nextBecoming)) {
            return new SingleDestination($nextBecoming);
        }

        return new MultipleDestination($nextBecoming);
    }
}
