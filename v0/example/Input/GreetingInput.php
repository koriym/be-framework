<?php

declare(strict_types=1);

namespace Be\Example\Input;

use Be\Example\Being\BeGreeting;
use Be\Framework\Attribute\Be;
use Be\Framework\SemanticVariable\SemanticValidator;
use Be\Example\Being\FormalGreeting;
use Be\Example\Being\CasualGreeting;
use Be\Example\Reason\FormalStyle;
use Be\Example\Reason\CasualStyle;

/**
 * Input entity that initiates greeting metamorphosis based on contextual factors
 *
 * @link https://schema.org/Action Action schema
 * @link https://schema.org/CommunicateAction Communication action schema
 * @see https://schema.org/agent
 * @see https://schema.org/instrument
 */
#[Be([BeGreeting::class])]
final readonly class GreetingInput
{
    public function __construct(
        public string $name,
        public string $style // 'formal' or 'casual'
    ) {
    }
}
