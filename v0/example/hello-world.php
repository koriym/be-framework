<?php

declare(strict_types=1);

namespace Be\Example;

$loader = require dirname(__DIR__) . '/vendor/autoload.php';
$loader->addPsr4('Be\\Example\\', __DIR__);

use Be\Framework\Becoming;
use Be\Framework\Exception\SemanticVariableException;
use Be\Example\Input\GreetingInput;
use Ray\Di\Injector;
use function get_class;
use function var_dump;

// Execute metamorphosis with new structure
$becoming = new Becoming(new Injector(), __NAMESPACE__ . '\\Ontology');

$formalInput = new GreetingInput('Smith', 'formal');
$formalGreeting = $becoming($formalInput);
echo "✅ Formal existence:\n" . json_encode($formalGreeting, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

$casualInput = new GreetingInput('Alice', 'casual');
$casualGreeting = $becoming($casualInput);
echo "✅ Casual existence:\n" . json_encode($casualGreeting, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

$input = new GreetingInput('', 'casual');
try {
    $becoming($input);
} catch (SemanticVariableException $e) {
    $exceptionType = get_class($e->getErrors()->exceptions[0]);
    $errorMessages = $e->getErrors()->getMessages('ja');
    echo "✅ $exceptionType: {$errorMessages[0]}\n";
}

$input = new GreetingInput('郡山 昭仁', 'casual');
try {
    $becoming($input);
} catch (SemanticVariableException $e) {
    $exceptionType = get_class($e->getErrors()->exceptions[0]);
    $errorMessages = $e->getErrors()->getMessages('ja');
    echo "✅ $exceptionType: {$errorMessages[0]}\n";
}

