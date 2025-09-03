<?php

require_once 'vendor/autoload.php';

use Be\Framework\SemanticLog\Logger;
use Be\Framework\BecomingArguments;
use Koriym\SemanticLogger\SemanticLogger;
use Ray\Di\Injector;
use Be\Framework\Be;

#[Be(\Be\Framework\FakeProcessedData::class)]
final class TestInput
{
    public function __construct(
        public readonly string $data
    ) {}
}

echo "=== testArrayBecomingSkipped Forward Trace ===\n";

$semanticLogger = new SemanticLogger();
$injector = new Injector();
$becomingArguments = new BecomingArguments($injector);
$logger = new Logger($semanticLogger, $becomingArguments);

$input = new TestInput('test data');

echo "1. Calling logger->open with array becoming (should be skipped)...\n";
$openId = $logger->open($input, ['Class1', 'Class2']);
echo "OpenId result: '" . $openId . "' (should be empty string)\n";

echo "2. Final semantic data (should be empty):\n";
var_dump($semanticLogger->toArray());

echo "=== testArrayBecomingSkipped Complete ===\n";