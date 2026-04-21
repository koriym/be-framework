<?php

declare(strict_types=1);

namespace Be\Framework\SemanticLog;

use Be\Framework\Attribute\Be;
use Be\Framework\BecomingArguments;
use Be\Framework\FakeProcessedData;
use Be\Framework\SemanticVariable\NullValidator;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Validator;
use Koriym\SemanticLogger\SemanticLogger;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

use function assert;
use function file_get_contents;
use function is_array;
use function json_decode;
use function json_encode;

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

        $openId = $this->logger->open($input, FakeProcessedData::class, ['data' => 'test data']);
        $this->assertNotEmpty($openId);

        $result = new FakeProcessedData('test data');
        $this->logger->close($result, $openId);

        $logData = $this->semanticLogger->toArray();
        assert(is_array($logData['open']) && is_array($logData['open'][0]) && is_array($logData['open'][0]['context']));
        $openContext = $logData['open'][0]['context'];

        // FakeProcessedData has no #[Be] → terminal target → being_final_open
        $this->assertEquals('being_final_open', $logData['open'][0]['type']);

        // Short keys — being-final-open schema
        $this->assertArrayHasKey('from', $openContext);
        $this->assertArrayHasKey('final', $openContext);
        $this->assertArrayHasKey('input', $openContext);
        $this->assertArrayHasKey('inject', $openContext);

        $this->assertIsString($openContext['from']);
        $this->assertIsString($openContext['final']);

        $this->assertEquals(TestInputForSchema::class, $openContext['from']);
        $this->assertEquals(FakeProcessedData::class, $openContext['final']);
        // jsonSerialize wraps assoc maps in stdClass so empty/nested values serialize as JSON objects.
        $this->assertEquals(
            (object) ['data' => 'Be\Framework\SemanticLog\TestInputForSchema::data'],
            $openContext['input'],
        );
    }

    public function testCloseContextSchemaCompliance(): void
    {
        $input = new TestInputForSchema('test data');
        $openId = $this->logger->open($input, FakeProcessedData::class, ['data' => 'test data']);

        $result = new FakeProcessedData('test data');
        $this->logger->close($result, $openId);

        $logData = $this->semanticLogger->toArray();
        assert(is_array($logData['close']) && is_array($logData['close'][0]) && is_array($logData['close'][0]['context']));
        $closeData = $logData['close'][0];
        $closeContext = $closeData['context'];

        // FakeProcessedData has no further #[Be] → being_final_close
        $this->assertEquals('being_final_close', $closeData['type']);
        $this->assertArrayHasKey('prop', $closeContext);
        $this->assertArrayHasKey('final', $closeContext);
        $this->assertEquals(FakeProcessedData::class, $closeContext['final']);
    }

    public function testJSONSchemaValidation(): void
    {
        $input = new TestInputForSchema('test data');
        $openId = $this->logger->open($input, FakeProcessedData::class, ['data' => 'test data']);

        $result = new FakeProcessedData('test data');
        $this->logger->close($result, $openId);

        $logData = $this->semanticLogger->toArray();

        $validator = new Validator();

        // FakeProcessedData has no #[Be], so the open context is the being-final-open form.
        $openSchema = json_decode(file_get_contents(__DIR__ . '/../../docs/schemas/being-final-open.json'));
        $openContext = json_decode(json_encode($logData['open'][0]['context']));
        $validator->validate($openContext, $openSchema, Constraint::CHECK_MODE_NORMAL);
        $this->assertTrue(
            $validator->isValid(),
            'being_final_open context should validate. Errors: ' . json_encode($validator->getErrors()),
        );

        $finalSchema = json_decode(file_get_contents(__DIR__ . '/../../docs/schemas/being-final-close.json'));
        $closeContext = json_decode(json_encode($logData['close'][0]['context']));
        $validator->reset();
        $validator->validate($closeContext, $finalSchema, Constraint::CHECK_MODE_NORMAL);
        $this->assertTrue(
            $validator->isValid(),
            'being_final_close context should validate. Errors: ' . json_encode($validator->getErrors()),
        );
    }
}
