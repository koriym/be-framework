<?php

declare(strict_types=1);

namespace Be\Framework\SemanticLog;

use Be\Framework\Attribute\Be;
use Be\Framework\BecomingArguments;
use Be\Framework\FakeProcessedData;
use Be\Framework\SemanticVariable\NullValidator;
use Koriym\SemanticLogger\SemanticLogger;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

use function assert;
use function is_array;

#[Be(FakeProcessedData::class)]
final class TestInputForSchema
{
    public function __construct(
        public readonly string $data,
    ) {
    }
}


final class SchemaComplianceTest extends TestCase
{
    private Logger $logger;
    private SemanticLogger $semanticLogger;

    protected function setUp(): void
    {
        $this->semanticLogger = new SemanticLogger();
        $injector = new Injector();
        $nullValidator = new NullValidator();
        $becomingArguments = new BecomingArguments($injector, $nullValidator);

        $this->logger = new Logger(
            $this->semanticLogger,
            $becomingArguments,
        );
    }

    public function testOpenContextSchemaCompliance(): void
    {
        $input = new TestInputForSchema('test data');

        $openId = $this->logger->open($input, FakeProcessedData::class);
        $this->assertNotEmpty($openId);

        $result = new FakeProcessedData('test data');
        $this->logger->close($result, $openId);

        $logData = $this->semanticLogger->toArray();
        assert(is_array($logData['open']) && is_array($logData['open']['context']));
        $openContext = $logData['open']['context'];

        // Short keys — becoming-open schema
        $this->assertArrayHasKey('from', $openContext);
        $this->assertArrayHasKey('be', $openContext);
        $this->assertArrayHasKey('input', $openContext);
        $this->assertArrayHasKey('inject', $openContext);

        $this->assertIsString($openContext['from']);
        $this->assertIsString($openContext['be']);

        $this->assertEquals(TestInputForSchema::class, $openContext['from']);
        $this->assertEquals(FakeProcessedData::class, $openContext['be']);
        $this->assertEquals(['data' => 'Be\Framework\SemanticLog\TestInputForSchema::data'], $openContext['input']);
    }

    public function testCloseContextSchemaCompliance(): void
    {
        $input = new TestInputForSchema('test data');
        $openId = $this->logger->open($input, FakeProcessedData::class);

        $result = new FakeProcessedData('test data');
        $this->logger->close($result, $openId);

        $logData = $this->semanticLogger->toArray();
        assert(is_array($logData['close']) && is_array($logData['close']['context']));
        $closeData = $logData['close'];
        $closeContext = $closeData['context'];

        // FakeProcessedData has no further #[Be] → becoming_final
        $this->assertEquals('becoming_final', $closeData['type']);
        $this->assertArrayHasKey('prop', $closeContext);
        $this->assertArrayHasKey('final', $closeContext);
        $this->assertEquals(FakeProcessedData::class, $closeContext['final']);
    }

    public function testJSONSchemaValidation(): void
    {
        $input = new TestInputForSchema('test data');
        $openId = $this->logger->open($input, FakeProcessedData::class);

        $result = new FakeProcessedData('test data');
        $this->logger->close($result, $openId);

        $logData = $this->semanticLogger->toArray();
        $openContext = $logData['open']['context'];

        // Short-key structural validation
        $this->assertArrayHasKey('from', $openContext);
        $this->assertArrayHasKey('be', $openContext);
        $this->assertArrayHasKey('input', $openContext);
        $this->assertArrayHasKey('inject', $openContext);
    }
}
