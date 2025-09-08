<?php

declare(strict_types=1);

namespace Be\Example\Reason;

use function array_rand;

/**
 * Casual communication style ontology defining informal interaction patterns
 *
 * @link https://schema.org/SocialInteraction Social interaction schema
 * @link https://schema.org/InformalCommunication Informal communication schema
 * @see https://schema.org/friendlyTone
 * @see https://schema.org/casualLanguage
 */
final readonly class CasualStyle
{
    public function casualGreeting(string $name): string
    {
        return "Hey {$name}! What's up?";
    }

    public function casualEmoji(): string
    {
        return ['🎉', '😎', '🚀', '✨'][array_rand(['🎉', '😎', '🚀', '✨'])];
    }
}
