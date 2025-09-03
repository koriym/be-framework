<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use Be\Framework\Attribute\Be;
use Be\Framework\Becoming;
use Ray\Di\Injector;
use Ray\InputQuery\Attribute\Input;

// Step 1: Raw greeting input
#[Be([FormalGreeting::class, CasualGreeting::class])]
final class GreetingInput
{
    public function __construct(
        public readonly string $name,
        public readonly string $style  // 'formal' or 'casual'
    ) {}
}

// Path A: Formal greeting for business context
final class FormalGreeting
{
    public function __construct(
        #[Input] string $name,
        #[Input] string $style
    ) {
        if ($style !== 'formal') {
            throw new InvalidArgumentException('Not a formal context');
        }
        $this->greeting = "Good day, Mr./Ms. {$name}. How may I assist you today?";
        $this->type = 'formal';
    }

    public readonly string $greeting;
    public readonly string $type;
}

// Path B: Casual greeting for friendly context
final class CasualGreeting
{
    public function __construct(
        #[Input] string $name,
        #[Input] string $style
    ) {
        if ($style !== 'casual') {
            throw new InvalidArgumentException('Not a casual context');
        }
        $this->greeting = "Hey {$name}! What's up?";
        $this->type = 'casual';
    }

    public readonly string $greeting;
    public readonly string $type;
}

// Execute the metamorphosis
$becoming = new Becoming(new Injector());

echo "=== Be Framework: Hello World with Branching ===\n\n";

// Example 1: Formal greeting
echo "Example 1: Formal Context\n";
$formalInput = new GreetingInput('Smith', 'formal');
$result1 = $becoming($formalInput);
echo "Type: " . $result1->type . PHP_EOL;
echo "Greeting: " . $result1->greeting . PHP_EOL;
echo "Final class: " . $result1::class . PHP_EOL . PHP_EOL;

// Example 2: Casual greeting
echo "Example 2: Casual Context\n";
$casualInput = new GreetingInput('Alice', 'casual');
$result2 = $becoming($casualInput);
echo "Type: " . $result2->type . PHP_EOL;
echo "Greeting: " . $result2->greeting . PHP_EOL;
echo "Final class: " . $result2::class . PHP_EOL;
