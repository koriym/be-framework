<?php

declare(strict_types=1);

namespace Be\Framework\SemanticLog;

use Be\Framework\BecomingArgumentsInterface;
use Be\Framework\BeingClass;
use Be\Framework\SemanticLog\Context\DestinationNotFound;
use Be\Framework\SemanticLog\Context\FinalDestination;
use Be\Framework\SemanticLog\Context\MetamorphosisCloseContext;
use Be\Framework\SemanticLog\Context\MetamorphosisOpenContext;
use Be\Framework\SemanticLog\Context\MultipleDestination;
use Be\Framework\SemanticLog\Context\SingleDestination;
use Koriym\SemanticLogger\SemanticLoggerInterface;

use function get_object_vars;
use function is_object;
use function is_string;
/**
 * Be Framework Logger
 *
 * Handles all semantic logging concerns, keeping Becoming engine clean.
 */
final class Logger implements LoggerInterface
{
    private BeingClass $getClass;

    public function __construct(
        private SemanticLoggerInterface $logger,
        private BecomingArgumentsInterface $becomingArguments,
    ) {
        $this->getClass = new BeingClass();
    }

    /**
     * Log transformation start
     */
    public function open(object $current, string|array $becoming): string
    {
        // Only handle single transformations for now
        if (! is_string($becoming)) {
            return ''; // Skip logging for array case
        }

        $fromClass = $current::class;
        $beAttribute = "#[Be({$becoming}::class)]";

        $args = ($this->becomingArguments)($current, $becoming);
        $immanentSources = $this->extractImmanentSources($current, $args);
        $transcendentSources = $this->extractTranscendentSources($args);

        return $this->logger->open(new MetamorphosisOpenContext(
            fromClass: $fromClass,
            beAttribute: $beAttribute,
            immanentSources: $immanentSources,
            transcendentSources: $transcendentSources,
        ));
    }

    /**
     * Log transformation completion
     */
    public function close(object|null $result, string $openId, string|null $error = null): void
    {
        // Skip if no open ID (array case not logged)
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

        // Map each arg back to its source property
        foreach ($args as $paramName => $value) {
            // Find matching property in current object
            foreach ($properties as $propName => $propValue) {
                if ($propValue === $value) {
                    $immanentSources[$paramName] = $current::class . '::' . $propName;
                    break;
                }
            }
        }

        return $immanentSources;
    }

    private function extractTranscendentSources(array $args): array
    {
        $transcendentSources = [];

        // For now, detect injected services by checking if value is an object
        // and doesn't match simple scalar types
        foreach ($args as $paramName => $value) {
            if (is_object($value)) {
                $transcendentSources[$paramName] = $value::class;
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
        $nextBecoming = ($this->getClass)($result);

        if ($nextBecoming === null) {
            return new FinalDestination($result::class);
        }

        if (is_string($nextBecoming)) {
            return new SingleDestination($nextBecoming);
        }

        return new MultipleDestination($nextBecoming);
    }
}
