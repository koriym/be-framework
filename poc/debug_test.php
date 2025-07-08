<?php

require_once 'vendor/autoload.php';

use Ray\Framework\FakeInputData;
use Ray\Framework\FakeProcessingStep;

// Create the objects step by step to debug
$input = new FakeInputData('hello world');
echo "1. FakeInputData properties:\n";
var_dump(get_object_vars($input));

$processing = new FakeProcessingStep('hello world');
echo "\n2. FakeProcessingStep properties:\n";
var_dump(get_object_vars($processing));

echo "\n3. FakeProcessingStep result object:\n";
var_dump($processing->result);

echo "\n4. FakeResult properties:\n";
var_dump(get_object_vars($processing->result));