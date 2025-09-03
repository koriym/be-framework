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

    public function __invoke(object $input): object
    {
        $state = $input;

        // The core metamorphosis loop - life as continuous becoming
        while ($next = $this->being->willBe($state)) {
            $state = $this->being->metamorphose($state, $next);
        }

        return $state;
    }
}
