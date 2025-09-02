<?php

declare(strict_types=1);

namespace Be\Framework;

use Be\Framework\SemanticLog\LoggerInterface;
use Ray\Di\InjectorInterface;
use ReflectionClass;
use Throwable;

use function implode;
use function is_string;
use function sprintf;

/**
 * The Be Framework - Metamorphic Programming Engine
 *
 * "Objects undergo metamorphosis through constructor injection -
 * a continuous process of becoming."
 */
final class Becoming
{
    private BeingClass $getClass;
    private BecomingArgumentsInterface $becomingArguments;

    public function __construct(
        private InjectorInterface $injector,
        BecomingArgumentsInterface|null $becomingArguments = null,
        private LoggerInterface|null $logger = null,
    ) {
        $this->getClass = new BeingClass();
        $this->becomingArguments = $becomingArguments ?? new BecomingArguments($this->injector);
    }

    public function __invoke(object $input): object
    {
        $current = $input;

        // The core metamorphosis loop - life as continuous becoming
        while ($becoming = ($this->getClass)($current)) {
            $current = $this->metamorphose($current, $becoming);
        }

        return $current;
    }

    /**
     * The moment of transformation - pure and irreversible
     */
    private function metamorphose(object $current, string|array $becoming): object
    {
        if (is_string($becoming)) {
            return $this->performSingleTransformation($current, $becoming);
        }

        // Array case: try each possibility until one succeeds
        foreach ($becoming as $class) {
            try {
                $args = ($this->becomingArguments)($current, $class);
                return (new ReflectionClass($class))->newInstanceArgs($args);
            } catch (Throwable) {
                continue; // Natural selection - try the next possibility
            }
        }

        throw new Exception\TypeMatchingFailure(
            sprintf('No matching class for becoming in [%s]', implode(', ', $becoming)),
        );
    }

    private function performSingleTransformation(object $current, string $becoming): object
    {
        $openId = $this->logger?->open($current, $becoming);

        try {
            $args = ($this->becomingArguments)($current, $becoming);
            $result = (new ReflectionClass($becoming))->newInstanceArgs($args);

            $this->logger?->close($result, $openId);
            return $result;

        } catch (Throwable $e) {
            $this->logger?->close(null, $openId, $e->getMessage());
            throw $e;
        }
    }
}
