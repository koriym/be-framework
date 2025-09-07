<?php

declare(strict_types=1);

namespace Be\Example\Being;

use Be\Example\Reason\CasualStyle;
use Ray\InputQuery\Attribute\Input;

/**
 * Casual greeting being representing informal communication entity
 *
 * @link https://schema.org/SocialMediaPosting Social media posting schema
 * @link https://schema.org/Message Message schema
 * @see https://schema.org/emoji
 * @see https://schema.org/informalCommunication
 */
final readonly class CasualGreeting
{
    public string $greeting;
    public string $emoji;

    public function __construct(
        #[Input] public string $name,
        #[Input] public CasualStyle $being
    ) {
        $this->greeting = $being->casualGreeting($name);
        $this->emoji = ['🎉', '😎', '🚀', '✨'][array_rand(['🎉', '😎', '🚀', '✨'])];  // 自己実現
    }
}
