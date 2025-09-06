<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use Be\Framework\Attribute\Be;
use Be\Framework\Becoming;
use Ray\Di\Injector;
use Ray\InputQuery\Attribute\Input;

// Style value objects for type-driven metamorphosis
final readonly class FormalStyle
{
    public function __construct(public string $value) {}
}

final readonly class CasualStyle  
{
    public function __construct(public string $value) {}
}

// Step 1: Raw greeting input discovers its destiny
#[Be([FormalGreeting::class, CasualGreeting::class])]
final class GreetingInput
{
    public readonly FormalStyle|CasualStyle $being;
    
    public function __construct(
        public readonly string $name,
        string $style  // 'formal' or 'casual'
    ) {
        // The existential question: Who will I become?
        $this->being = ($style === 'formal')
            ? new FormalStyle($style)
            : new CasualStyle($style);
    }
}

// Path A: Formal greeting for business context
final class FormalGreeting
{
    public function __construct(
        #[Input] string $name,
        #[Input] FormalStyle $being  // Type-driven selection
    ) {
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
        #[Input] CasualStyle $being  // Type-driven selection
    ) {
        $this->greeting = "Hey {$name}! What's up?";
        $this->type = 'casual';
    }

    public readonly string $greeting;
    public readonly string $type;
}

// Execute the metamorphosis
$becoming = new Becoming(new Injector(), __NAMESPACE__);

echo "=== Be Framework: Hello World with Branching ===\n\n";
$formalInput = new GreetingInput('Smith', 'formal');
$formalForm = $becoming($formalInput);
$casualInput = new GreetingInput('Alice', 'casual');
$casuallForm = $becoming($casualInput);

echo "✅hello.php (formal):" . PHP_EOL;;
echo " input: " . json_encode($formalInput) . PHP_EOL;
echo " output: " . json_encode($formalForm) . PHP_EOL;
echo "✅hello.php (casual):" . PHP_EOL;;
echo " input: " . json_encode($casualInput) . PHP_EOL;
echo " output: " . json_encode($casuallForm) . PHP_EOL;
