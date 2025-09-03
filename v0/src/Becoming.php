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
 * Objects undergo metamorphosis through constructor injection - a continuous process of becoming.
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
     * Life as continuous becoming
     *
     * No man ever steps in the same river twice - everything flows, everything changes.
     * Each moment births what was always waiting to emerge.
     *
     * @param object $input The initial state of being
     * @return object The final actualized form
     */
    public function __invoke(object $input): object
    {
        $current = $input;

        // Being reveals its becoming, then becomes it
        while ($nextForm = $this->being->willBe($current)) {
            $current = $this->being->metamorphose($current, $nextForm);
        }

        return $current;
    }
}
