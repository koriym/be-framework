<?php

declare(strict_types=1);

namespace Be\Example\Being;

use Be\Example\Reason\FormalStyle;
use Be\Example\Tag\English;
use Be\Framework\SemanticVariable\SemanticValidator;
use Be\Framework\Exception\SemanticVariableException;
use Ray\InputQuery\Attribute\Input;

/**
 * Formal greeting being representing business communication entity
 *
 * @link https://schema.org/Message Message schema
 * @link https://schema.org/BusinessEvent Business event schema
 * @see https://schema.org/businessFunction
 * @see https://schema.org/communicationStyle
 */
final readonly class FormalGreeting
{
    public string $greeting;
    public string $businessCard;

    public function __construct(
        #[Input] #[English] public string $name,         // Immanent
        #[Input] public FormalStyle $being    // Transcendent
    ) {
        // Semantic validartion during metamorphosis
        $validator = new SemanticValidator('Be\\Example\\Ontology');
        $errors = $validator->validate('name', $name);
        if ($errors->hasErrors()) {
            throw new SemanticVariableException($errors);
        }

        $this->greeting = $being->formalGreeting($name);
        $this->businessCard = $being->formalBusinessCard($name);
    }
}
