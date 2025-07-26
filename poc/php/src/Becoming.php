<?php

declare(strict_types=1);

namespace Be\Framework;

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
final class Becoming implements MetamorphosisInterface
{
    private GetClass $getClass;
    private BecomingArgumentsInterface $becomingArguments;

    public function __construct(
        private InjectorInterface $injector,
        BecomingArgumentsInterface|null $becomingArguments = null,
    ) {
        $this->getClass = new GetClass();
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
            $args = ($this->becomingArguments)($current, $becoming);

            return (new ReflectionClass($becoming))->newInstanceArgs($args);
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
}
