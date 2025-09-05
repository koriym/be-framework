<?php

declare(strict_types=1);

namespace Be\Framework;

use Be\Framework\SemanticLog\Logger;
use Be\Framework\SemanticLog\LoggerInterface;
use Be\Framework\SemanticVariable\NullValidator;
use Koriym\SemanticLogger\SemanticLogger;
use Override;
use Ray\Di\InjectorInterface;

/**
 * The Be Framework - Metamorphic Programming Engine
 *
 * Objects undergo metamorphosis through constructor injection - a continuous process of becoming.
 */
final class Becoming implements BecomingInterface
{
    private Being $being;

    public function __construct(
        InjectorInterface $injector,
        LoggerInterface|null $logger = null,
        BecomingArgumentsInterface|null $becomingArguments = null,
    ) {
        $becomingArguments ??= new BecomingArguments($injector, new NullValidator());
        $logger ??= new Logger(new SemanticLogger(), $becomingArguments);
        $this->being = new Being($logger, $becomingArguments);
    }

    /**
     * Life as continuous becoming
     *
     * No man ever steps in the same river twice - everything flows, everything changes.
     * Each moment births what was always waiting to emerge.
     *
     * @param object $input The initial state of being
     *
     * @return object The final actualized form
     */
    #[Override]
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
