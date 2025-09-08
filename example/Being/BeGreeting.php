<?php

declare(strict_types=1);

namespace Be\Example\Being;

use Be\Example\Reason\CasualStyle;
use Be\Example\Reason\FormalStyle;
use Be\Framework\Attribute\Be;
use Be\Framework\SemanticVariable\SemanticValidator;
use Be\Framework\Exception\SemanticVariableException;
use Be\Example\Tag\English;
use Ray\InputQuery\Attribute\Input;

#[Be([FormalGreeting::class, CasualGreeting::class])]
final readonly class BeGreeting
{
    public CasualStyle|FormalStyle $being;

    public function __construct(
        #[Input] #[English] public string $name,
        #[Input] string $style
    ) {
        $this->being = $style == 'formal' ? new FormalStyle() : new CasualStyle();
    }
}
