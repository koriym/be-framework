<?php

require 'vendor/autoload.php';

use Ray\InputQuery\Attribute\Input;
use Ray\Di\Attribute\Inject;

// Load the test classes
require_once 'tests/BecomingTest.php';

$reflection = new ReflectionClass('Be\Framework\BecomingTestBothAttributes');
$constructor = $reflection->getConstructor();
$param = $constructor->getParameters()[0];

echo "Parameter name: " . $param->getName() . "\n";

$inputAttrs = $param->getAttributes(Input::class);
$injectAttrs = $param->getAttributes(Inject::class);

echo "Input attributes count: " . count($inputAttrs) . "\n";
echo "Inject attributes count: " . count($injectAttrs) . "\n";

// Debug the actual attributes
echo "\nAll attributes:\n";
foreach ($param->getAttributes() as $attr) {
    echo "- " . $attr->getName() . "\n";
}

// Test condition
$hasInput = !empty($inputAttrs);
$hasInject = !empty($injectAttrs);

echo "\nConditions:\n";
echo "hasInput: " . ($hasInput ? 'true' : 'false') . "\n";
echo "hasInject: " . ($hasInject ? 'true' : 'false') . "\n";
echo "Both: " . (($hasInput && $hasInject) ? 'true' : 'false') . "\n";