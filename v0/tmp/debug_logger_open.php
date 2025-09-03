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

echo "Testing Logger->open method step by step\n";

$semanticLogger = new SemanticLogger();
$injector = new Injector();
$becomingArguments = new BecomingArguments($injector);
$logger = new Logger($semanticLogger, $becomingArguments);

$input = new TestInput('test data');
$nextBecoming = \Be\Framework\FakeProcessedData::class;

echo "Calling logger->open with:\n";
echo "- input: TestInput with data='test data'\n";
echo "- nextBecoming: " . $nextBecoming . "\n\n";

$openId = $logger->open($input, $nextBecoming);

echo "Result openId: " . $openId . "\n";
echo "SemanticLogger data:\n";
var_dump($semanticLogger->toArray());