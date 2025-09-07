<?php

declare(strict_types=1);

namespace Be\Example;

$loader = require 'vendor/autoload.php';
$loader->addPsr4('Be\\Example\\', __DIR__);

use Be\Framework\Becoming;
use Be\Example\Input\GreetingInput;
use Ray\Di\Injector;

// Execute metamorphosis with new structure
$becoming = new Becoming(new Injector(), __NAMESPACE__);

$formalInput = GreetingInput::create('Smith', 'formal');
$formalGreeting = $becoming($formalInput);
echo "✅ Formal existence:\n" . json_encode($formalGreeting, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

$casualInput = GreetingInput::create('Alice', 'casual');
$casualGreeting = $becoming($casualInput);
echo "✅ Casual existence:\n" . json_encode($casualGreeting, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

echo "✅ Both transformations completed successfully!\n";