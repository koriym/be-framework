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

echo "=== testSingleTransformationLogging Forward Trace ===\n";

$semanticLogger = new SemanticLogger();
$injector = new Injector();
$becomingArguments = new BecomingArguments($injector);
$logger = new Logger($semanticLogger, $becomingArguments);

$input = new TestInput('test data');

echo "1. Calling logger->open...\n";
$openId = $logger->open($input, \Be\Framework\FakeProcessedData::class);
echo "OpenId: " . $openId . "\n";

echo "2. Creating result object...\n";
$result = new \stdClass();
$result->processedData = 'test result';

echo "3. Calling logger->close...\n";  
$logger->close($result, $openId);

echo "4. Final semantic data:\n";
var_dump($semanticLogger->toArray());

echo "=== testSingleTransformationLogging Complete ===\n";