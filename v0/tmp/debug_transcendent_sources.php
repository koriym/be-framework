<?php

require_once 'vendor/autoload.php';

use Be\Framework\SemanticLog\Logger;
use Be\Framework\BecomingArguments;
use Koriym\SemanticLogger\SemanticLogger;
use Ray\Di\Injector;

echo "Testing extractTranscendentSources with actual objects\n";

$semanticLogger = new SemanticLogger();
$injector = new Injector();
$becomingArguments = new BecomingArguments($injector);
$logger = new Logger($semanticLogger, $becomingArguments);

// Test the private extractTranscendentSources method directly using reflection
$reflection = new ReflectionClass($logger);
$method = $reflection->getMethod('extractTranscendentSources');
$method->setAccessible(true);

// Create a test input with actual objects that would be passed through extractTranscendentSources
$testObject = new stdClass();
$testObject->data = 'test';

$injectorInstance = new Injector();

$args = [
    'stringParam' => 'test string',
    'numberParam' => 42,
    'testObject' => $testObject,      // This should hit is_object condition
    'injector' => $injectorInstance,  // This should also hit is_object condition
    'nullParam' => null,
    'arrayParam' => ['test']
];

echo "Testing extractTranscendentSources with args containing objects:\n";
foreach ($args as $key => $value) {
    echo "  $key: " . (is_object($value) ? get_class($value) : gettype($value)) . "\n";
}

echo "\nCalling extractTranscendentSources...\n";
$result = $method->invoke($logger, $args);

echo "Result:\n";
var_dump($result);

echo "\nExpected to contain testObject and injector class names\n";