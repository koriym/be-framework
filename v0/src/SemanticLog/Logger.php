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
use JsonException;
use Koriym\SemanticLogger\SemanticLoggerInterface;
use Override;
use Ray\Di\Di\Inject;
use ReflectionClass;
use Throwable;

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
        $this->being = new Being($this, $this->becomingArguments);
    }

    /**
     * Log transformation start
     *
     * @param string|array<string> $becoming
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
     * @param array<string, mixed> $args
     *
     * @return array<string, string>
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
     * @param array<string, mixed> $args
     *
     * @return array<string, string>
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

    /** @return array<string, mixed> */
    private function extractProperties(object $result): array
    {
        // @todo Handle uninitialized properties in Accept pattern objects
        // @todo Privacy/Security: Consider extracting shape-only metadata instead of actual values
        //       For production use, should emit property names + types only, not raw values
        //       e.g., ['email' => 'string', 'validated' => 'bool'] instead of actual data
        // For now, get_object_vars() covers all realistic Be Framework objects
        return get_object_vars($result);
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
