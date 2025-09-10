<?php

declare(strict_types=1);

namespace Be\Framework;

use Be\Framework\SemanticLog\Logger;
use Be\Framework\SemanticLog\LoggerInterface;
use Be\Framework\SemanticVariable\SemanticValidator;
use Koriym\SemanticLogger\SemanticLogger;
use Override;
use Ray\Di\Di\Named;
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
        #[Named('semantic_namespace')]
        string $semanticNamespace = 'Be\App\Semantic',
        LoggerInterface|null $logger = null,
        BecomingArgumentsInterface|null $becomingArguments = null,
    ) {
        $becomingArguments ??= new BecomingArguments($injector, new SemanticValidator($semanticNamespace));
        $logger ??= new Logger(new SemanticLogger(), $becomingArguments);
        $this->being = new Being($logger, $becomingArguments, new BecomingType());
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
