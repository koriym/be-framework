<?php

declare(strict_types=1);

namespace Be\Framework\SemanticLog;

use Be\Framework\Attribute\Be;
use Be\Framework\BecomingArgumentsInterface;
use Be\Framework\BecomingType;
use Be\Framework\Being;
use Be\Framework\SemanticLog\Context\BecomingCloseContext;
use Be\Framework\SemanticLog\Context\BecomingOpenContext;
use Be\Framework\SemanticLog\Context\BeingCloseContext;
use Be\Framework\SemanticLog\Context\BeingErrorCloseContext;
use Be\Framework\SemanticLog\Context\BeingFinalCloseContext;
use Be\Framework\SemanticLog\Context\BeingFinalOpenContext;
use Be\Framework\SemanticLog\Context\BeingOpenContext;
use JsonException;
use Koriym\SemanticLogger\AbstractContext;
use Koriym\SemanticLogger\SemanticLoggerInterface;
use LogicException;
use Override;
use Ray\Di\Di\Inject;
use Ray\InputQuery\Attribute\Input;
use ReflectionClass;
use Throwable;

use function array_key_exists;
use function get_debug_type;
use function get_object_vars;
use function gettype;
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
    private ObjectPropertyExtractor $propertyExtractor;

    public function __construct(
        private SemanticLoggerInterface $logger,
        BecomingArgumentsInterface $becomingArguments,
    ) {
        $this->being = new Being($this, $becomingArguments, new BecomingType());
        $this->propertyExtractor = new ObjectPropertyExtractor();
    }

    /**
     * Open the outer span wrapping the whole metamorphosis chain.
     */
    #[Override]
    public function openChain(object $input): string
    {
        return $this->logger->open(new BecomingOpenContext(
            input: $input::class,
            prop: $this->extractProperties($input),
        ));
    }

    /**
     * Close the outer chain span.
     *
     * @throws LogicException When called with neither a terminal being nor an
     *                        exception — the close payload has no valid shape
     *                        under the `oneOf` constraint in
     *                        `becoming-close.json`, so we refuse to emit one.
     */
    #[Override]
    public function closeChain(object|null $final, string $openId, Throwable|null $exception = null, string|null $origin = null): void
    {
        if ($openId === '') {
            return;
        }

        if ($exception !== null) {
            $this->logger->close(new BecomingCloseContext(
                exit: BecomingCloseContext::EXIT_ERROR,
                error: $exception::class,
                message: $exception->getMessage(),
                origin: $origin,
            ), $openId);

            return;
        }

        if ($final === null) {
            throw new LogicException(
                'Logger::closeChain() requires a terminal being on success; got null with no exception.',
            );
        }

        $this->logger->close(new BecomingCloseContext(
            exit: BecomingCloseContext::EXIT_SUCCESS,
            final: $final::class,
        ), $openId);
    }

    /**
     * Log transformation start
     *
     * @param class-string         $becoming
     * @param array<string, mixed> $args
     */
    #[Override]
    public function open(object $current, string $becoming, array $args): string
    {
        $fromClass = $current::class;
        $input = $this->extractImmanentSources($current, $args, $becoming);
        $inject = $this->extractTranscendentSources($args, $becoming);

        if ($this->targetHasBeAttribute($becoming)) {
            return $this->logger->open(new BeingOpenContext(
                from: $fromClass,
                be: $becoming,
                input: $input,
                inject: $inject,
            ));
        }

        return $this->logger->open(new BeingFinalOpenContext(
            from: $fromClass,
            final: $becoming,
            input: $input,
            inject: $inject,
        ));
    }

    /**
     * Log transformation completion
     *
     * @throws LogicException When result is null without an exception (programming error).
     */
    #[Override]
    public function close(object|null $result, string $openId, Throwable|null $exception = null): void
    {
        if ($openId === '') {
            return;
        }

        if ($exception !== null) {
            $this->logger->close(new BeingErrorCloseContext(
                error: $exception::class,
                message: $exception->getMessage(),
            ), $openId);

            return;
        }

        if ($result === null) {
            throw new LogicException(
                'Logger::close() requires a result object on success; got null with no exception.',
            );
        }

        $prop = $this->extractProperties($result);
        $nextBecoming = $this->being->willBe($result);

        if ($nextBecoming === null) {
            $this->logger->close(new BeingFinalCloseContext(
                final: $result::class,
                prop: $prop,
                been: $this->extractBeenEvents($result),
            ), $openId);

            return;
        }

        $this->logger->close(new BeingCloseContext(
            prop: $prop,
            being: $result::class,
        ), $openId);
    }

    /** @param class-string $becoming */
    private function targetHasBeAttribute(string $becoming): bool
    {
        $reflection = new ReflectionClass($becoming);

        return $reflection->getAttributes(Be::class) !== [];
    }

    /**
     * Extract #[Input] parameter sources by inspecting the target constructor.
     *
     * A parameter belongs in immanent sources only when it carries `#[Input]` and the
     * current being exposes a property of the same name — mirroring extractTranscendentSources'
     * attribute-driven approach so non-input arguments that happen to share a name with a public
     * property don't leak in.
     *
     * @param ConstructorArguments $args
     * @phpstan-param array<string, mixed> $args
     *
     * @return ImmanentSources
     * @phpstan-return array<string, string>
     */
    private function extractImmanentSources(object $current, array $args, string $becoming): array
    {
        /** @var class-string $becoming */
        $constructor = (new ReflectionClass($becoming))->getConstructor();
        if ($constructor === null) {
            return [];
        }

        $properties = get_object_vars($current);
        $immanentSources = [];
        foreach ($constructor->getParameters() as $param) {
            $paramName = $param->getName();
            if (! array_key_exists($paramName, $args)) {
                continue;
            }

            if (empty($param->getAttributes(Input::class))) {
                continue;
            }

            if (! array_key_exists($paramName, $properties)) {
                continue;
            }

            $immanentSources[$paramName] = $current::class . '::' . $paramName;
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
     * Extract the event list curated by the terminal being into its own `Been`.
     *
     * A Final class may accept a `Been` via `#[Inject]` and grow it with `with()`
     * calls inside its constructor. Those calls write to the live semantic logger
     * AND accumulate on the returned `Been`. We reflect on the result to surface
     * those curated events on the `being_final_close` span — the logger reference
     * stays behind since only the events belong in the log payload.
     *
     * Returns `[]` when the result carries no `Been` property, or when the
     * carrier is empty.
     *
     * @return list<AbstractContext>
     */
    private function extractBeenEvents(object $result): array
    {
        foreach ((new ReflectionClass($result))->getProperties() as $property) {
            if (! $property->isPublic() || $property->isStatic()) {
                continue;
            }

            if (! $property->isInitialized($result)) {
                continue;
            }

            /** @psalm-suppress MixedAssignment */
            $value = $property->getValue($result);
            if ($value instanceof Been) {
                return $value->events;
            }
        }

        return [];
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
        return $this->propertyExtractor->extract($result);
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
