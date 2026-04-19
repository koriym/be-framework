<?php

declare(strict_types=1);

namespace Be\Framework\SemanticLog;

use Be\Framework\BecomingArgumentsInterface;
use Be\Framework\BecomingType;
use Be\Framework\Being;
use Be\Framework\SemanticLog\Context\DestinationNotFound;
use Be\Framework\SemanticLog\Context\FinalDestination;
use Be\Framework\SemanticLog\Context\MetamorphosisCloseContext;
use Be\Framework\SemanticLog\Context\MetamorphosisOpenContext;
use Be\Framework\SemanticLog\Context\MultipleDestination;
use Be\Framework\SemanticLog\Context\SingleDestination;
use JsonException;
use Koriym\SemanticLogger\SemanticLoggerInterface;
use Override;
use Ray\Di\Di\Inject;
use ReflectionClass;
use Throwable;

use function array_filter;
use function array_key_exists;
use function array_keys;
use function array_map;
use function get_debug_type;
use function get_object_vars;
use function gettype;
use function implode;
use function is_array;
use function is_bool;
use function is_numeric;
use function is_object;
use function is_string;
use function json_encode;
use function var_export;

use const JSON_INVALID_UTF8_SUBSTITUTE;
use const JSON_PARTIAL_OUTPUT_ON_ERROR;
use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_UNICODE;

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
        $this->being = new Being($this, $this->becomingArguments, new BecomingType());
    }

    /**
     * Log transformation start
     *
     * @param QualifiedClassName|QualifiedClasses $becoming
     * @phpstan-param string|array<string> $becoming
     */
    #[Override]
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
    #[Override]
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

    /**
     * @param ConstructorArguments $args
     * @phpstan-param array<string, mixed> $args
     *
     * @return ImmanentSources
     * @phpstan-return array<string, string>
     */
    private function extractImmanentSources(object $current, array $args): array
    {
        $immanentSources = [];
        $properties = get_object_vars($current);

        // Use parameter names for reliable mapping (BecomingArguments ensures parameter names match property names for #[Input])
        foreach (array_keys($args) as $paramName) {
            if (array_key_exists($paramName, $properties)) {
                $immanentSources[$paramName] = $current::class . '::' . $paramName;
            }
        }

        return $immanentSources;
    }

    /**
     * @param ConstructorArguments $args
     * @phpstan-param array<string, mixed> $args
     *
     * @return TranscendentSources
     * @phpstan-return array<string, string>
     */
    private function extractTranscendentSources(array $args, string $becoming): array
    {
        $transcendentSources = [];
        /** @var class-string $becoming */
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
                /** @var mixed $value */
                $value = $args[$paramName];

                // For objects, use their class name; for scalars, use their type/value representation
                if (is_object($value)) {
                    $transcendentSources[$paramName] = $value::class;
                    continue;
                }

                // For scalar/other types, show the type information
                $stringValue = match (true) {
                    is_string($value) => $value,
                    is_numeric($value) => (string) $value,
                    is_bool($value) => $value ? 'true' : 'false',
                    $value === null => 'null',
                    is_array($value) => $this->safeJsonEncode($value),
                    default => get_debug_type($value)
                };
                $transcendentSources[$paramName] = gettype($value) . ':' . $stringValue;
            }
        }

        return $transcendentSources;
    }

    /**
     * Extract object properties safely, handling uninitialized properties
     *
     * Handles both declared properties (with uninitialized property checks)
     * and dynamic properties (stdClass, objects with __set).
     *
     * @return ObjectProperties
     * @phpstan-return array<string, mixed>
     */
    private function extractProperties(object $result): array
    {
        // Exclude any `Been` property: it carries this very log and would
        // embed the event stream recursively inside the close context.
        // Start with dynamic properties (stdClass, objects with __set) which
        // already come back with string keys via get_object_vars().
        $properties = array_filter(
            get_object_vars($result),
            static fn (mixed $value): bool => ! ($value instanceof Been),
        );

        // Override/supplement with declared properties via reflection so
        // Accept-pattern objects with uninitialized properties are included.
        foreach ((new ReflectionClass($result))->getProperties() as $property) {
            if (! $property->isPublic()) {
                continue;
            }

            $name = $property->getName();

            // Handle uninitialized properties (Accept pattern objects may have these)
            if (! $property->isInitialized($result)) {
                $properties[$name] = null;
                continue;
            }

            /**
             * @psalm-suppress MixedAssignment
             * @var mixed $value
             */
            $value = $property->getValue($result);
            if ($value instanceof Been) {
                unset($properties[$name]);
                continue;
            }

            $properties[$name] = $value;
        }

        return $properties;
    }

    private function determineDestination(object $result): SingleDestination|MultipleDestination|FinalDestination
    {
        $nextBecoming = $this->being->willBe($result);

        if ($nextBecoming === null) {
            return new FinalDestination($result::class);
        }

        if (is_string($nextBecoming)) {
            return new SingleDestination($nextBecoming);
        }

        /** @var array<class-string> $nextBecoming */
        return new MultipleDestination($nextBecoming);
    }

    /**
     * Safely encode array as JSON, falling back to alternatives if encoding fails
     *
     * @param array<mixed> $value
     */
    private function safeJsonEncode(array $value): string
    {
        try {
            return json_encode(
                $value,
                JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE,
            );
        } catch (JsonException) {
            // First fallback: try with partial output on error
            $fallback = json_encode($value, JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_UNESCAPED_UNICODE);
            if ($fallback !== false) {
                return $fallback;
            }

            // Second fallback: try var_export
            try {
                return var_export($value, true);
            } catch (Throwable) {
                // Final fallback: simple description
                return '[unencodable array]';
            }
        }
    }
}
