<?php

declare(strict_types=1);

echo "=== Be Framework: Hello World Example ===\n\n";

echo "This example demonstrates Type-Driven Metamorphosis:\n";
echo "• FormalStyle vs CasualStyle value objects create different constructor signatures\n";
echo "• Framework selects the correct greeting class based on type matching\n";
echo "• No exceptions needed - pure type-driven selection\n\n";

require __DIR__ . '/hello-world.php';

echo "\n=== Example Complete ===\n";
echo "\nWhat happened:\n";
echo "1. GreetingInput creates FormalStyle or CasualStyle in \$being property\n";
echo "2. Framework examines FormalGreeting and CasualGreeting constructors\n";
echo "3. FormalGreeting(string, FormalStyle) vs CasualGreeting(string, CasualStyle)\n";
echo "4. Different parameter types = different signatures = Type-Driven selection!\n";
echo "5. This is the preferred pattern from the specification document.\n";