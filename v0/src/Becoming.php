<?php

declare(strict_types=1);

namespace Be\Framework;

use Be\Framework\SemanticLog\Logger;
use Be\Framework\SemanticLog\LoggerInterface;
use Koriym\SemanticLogger\SemanticLogger;
use Ray\Di\InjectorInterface;

/**
 * The Be Framework - Metamorphic Programming Engine
 *
 * "Objects undergo metamorphosis through constructor injection -
 * a continuous process of becoming."
 */
final class Becoming
{
    private Being $being;

    public function __construct(
        private InjectorInterface $injector,
        LoggerInterface|null $logger = null,
    ) {
        $becomingArguments = new BecomingArguments($this->injector);
        $logger ??= new Logger(new SemanticLogger(), $becomingArguments);
        $this->being = new Being($logger, new BecomingArguments($this->injector));
    }

    /**
     * Let existence flow:
     * the given state becomes what it already carries within,
     * transforming step by step until it rests (for now).
     * 
     * In this flow, each state holds its destiny,
     * revealed through willBe(), actualized through metamorphose().
     *
     * @param object $input The initial state of being
     * @return object The final actualized form
     */
    public function __invoke(object $input): object
    {
        $state = $input;

        // Life as continuous becoming: Being reveals its becoming, then becomes it
        while ($next = $this->being->willBe($state)) {
            $state = $this->being->metamorphose($state, $next);
        }

        return $state;
    }
}
