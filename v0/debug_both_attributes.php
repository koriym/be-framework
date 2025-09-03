<?php

require 'vendor/autoload.php';

use Ray\InputQuery\Attribute\Input;
use Ray\Di\Attribute\Inject;

class TestClass
{
    public function __construct(
        #[Input]
        #[Inject]
        string $data
    ) {
        echo "Constructor called with: $data\n";
    }
}

$reflection = new ReflectionClass(TestClass::class);
$constructor = $reflection->getConstructor();
$param = $constructor->getParameters()[0];

$inputAttrs = $param->getAttributes(Input::class);
$injectAttrs = $param->getAttributes(Inject::class);

echo "Input attributes count: " . count($inputAttrs) . "\n";
echo "Inject attributes count: " . count($injectAttrs) . "\n";

// Test if both are detected
$hasInput = !empty($inputAttrs);
$hasInject = !empty($injectAttrs);

echo "Has Input: " . ($hasInput ? 'true' : 'false') . "\n";
echo "Has Inject: " . ($hasInject ? 'true' : 'false') . "\n";
echo "Should throw exception: " . (($hasInput && $hasInject) ? 'true' : 'false') . "\n";