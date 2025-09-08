<?php

declare(strict_types=1);

namespace Be\Example\Reason;

/**
 * Formal communication style ontology defining business interaction patterns
 * 
 * @link https://schema.org/BusinessFunction Business function schema
 * @link https://schema.org/ProfessionalService Professional service schema
 * @see https://schema.org/businessCard
 * @see https://schema.org/formalProtocol
 */
final readonly class FormalStyle
{
    public function formalGreeting(string $name): string
    {
        return "Good day, Mr./Ms. {$name}. How may I assist you today?";
    }

    public function formalBusinessCard(string $name): string
    {
        return "┌─────────────────────────┐\n" .
               "│ 📋 BUSINESS CARD        │\n" .
               "│ Mr./Ms. {$name}         │\n" .
               "│ Protocol: Formal        │\n" .
               "│ Status: Professional    │\n" .
               "└─────────────────────────┘";
    }
}