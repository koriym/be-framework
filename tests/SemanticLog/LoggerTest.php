<?php

declare(strict_types=1);

namespace Be\Framework\SemanticLog;

use Be\Framework\Attribute\Be;
use Be\Framework\BecomingArguments;
use Be\Framework\ClassWithInjectObject;
use Be\Framework\FakeProcessedData;
use Be\Framework\NoConstructorClass;
use Be\Framework\SemanticVariable\NullValidator;
use Be\Framework\TestInputWithDependency;
use Be\Framework\TestMultipleDestination;
use Be\Framework\TestSingleDestination;
use Koriym\SemanticLogger\SemanticLogger;
use LogicException;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;
use ReflectionClass;
use RuntimeException;
use stdClass;

use function assert;
use function is_array;

#[Be(FakeProcessedData::class)]
final class TestInput
{
    public function __construct(
        public readonly string $data,
    ) {
    }
}


final class LoggerTest extends TestCase
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

    public function testSingleTransformationLogging(): void
    {
        $input = new TestInput('test data');

        $openId = $this->logger->open($input, FakeProcessedData::class, []);
        $this->assertNotEmpty($openId);

        $result = new stdClass();
        $result->processedData = 'test result';
        $this->logger->close($result, $openId);

        $logData = $this->semanticLogger->toArray();
        $this->assertArrayHasKey('open', $logData);
        $this->assertArrayHasKey('close', $logData);
        assert(is_array($logData['open']) && is_array($logData['close']));

        // Verify open log structure — short keys
        $openData = $logData['open'][0];
        assert(is_array($openData['context']));
        $this->assertEquals(TestInput::class, $openData['context']['from']);
        $this->assertEquals(FakeProcessedData::class, $openData['context']['final']);
        // FakeProcessedData has no #[Be] → terminal target → being_final_open
        $this->assertEquals('being_final_open', $openData['type']);

        // Verify close log structure — result has no further #[Be] so it's final.
        $closeData = $logData['close'][0];
        assert(is_array($closeData['context']));
        $this->assertArrayHasKey('prop', $closeData['context']);
        $this->assertArrayHasKey('final', $closeData['context']);
    }

    public function testOpenWithIntermediateBeingTarget(): void
    {
        // TestSingleDestination carries #[Be(FakeProcessedData::class)] — intermediate target
        $input = new TestInput('test data');

        $openId = $this->logger->open($input, TestSingleDestination::class, []);
        $this->assertNotEmpty($openId);

        $this->logger->close(new stdClass(), $openId);

        $logData = $this->semanticLogger->toArray();
        assert(is_array($logData['open']));
        $openData = $logData['open'][0];
        assert(is_array($openData['context']));
        $this->assertEquals('being_open', $openData['type']);
        $this->assertEquals(TestSingleDestination::class, $openData['context']['be']);
        // Empty maps are emitted as stdClass so the JSON form is "{}" rather than "[]".
        $this->assertEquals(new stdClass(), $openData['context']['input']);
        $this->assertEquals(new stdClass(), $openData['context']['inject']);
    }

    public function testErrorLogging(): void
    {
        $input = new TestInput('test data');

        $openId = $this->logger->open($input, FakeProcessedData::class, []);

        $exception = new RuntimeException('Test error message');
        $this->logger->close(null, $openId, $exception);

        $logData = $this->semanticLogger->toArray();
        assert(is_array($logData['close']));
        $closeData = $logData['close'][0];
        assert(is_array($closeData) && is_array($closeData['context']));
        $this->assertEquals('being_error_close', $closeData['type']);
        $this->assertEquals(RuntimeException::class, $closeData['context']['error']);
        $this->assertEquals('Test error message', $closeData['context']['message']);
    }

    public function testErrorLoggingWithoutException(): void
    {
        $input = new TestInput('test data');

        $openId = $this->logger->open($input, FakeProcessedData::class, []);

        // Null result without exception — still closes as error with 'Unknown error'
        $this->logger->close(null, $openId);

        $logData = $this->semanticLogger->toArray();
        assert(is_array($logData['close']));
        $closeData = $logData['close'][0];
        $this->assertEquals('being_error_close', $closeData['type']);
        $this->assertEquals('Unknown error', $closeData['context']['message']);
    }

    public function testEmptyOpenIdSkip(): void
    {
        // close() with empty openId returns early without error
        $this->logger->close(new stdClass(), '');
        $this->assertTrue(true);
    }

    public function testCloseChainRejectsNullFinalWithoutException(): void
    {
        // closeChain(null, $id) with no exception has no valid close-payload shape
        // under the becoming-close oneOf schema — refuse rather than emit empty {}.
        $chainId = $this->logger->openChain(new TestInput('data'));

        $this->expectException(LogicException::class);
        $this->logger->closeChain(null, $chainId);
    }

    public function testOpenChainLogsInputProps(): void
    {
        $chainId = $this->logger->openChain(new TestInput('data'));
        $this->logger->closeChain(new FakeProcessedData('done'), $chainId);

        $this->assertNotSame('', $chainId);

        $logData = $this->semanticLogger->toArray();
        assert(is_array($logData['open']));
        $openData = $logData['open'][0];
        assert(is_array($openData) && is_array($openData['context']));

        $this->assertSame('becoming_open', $openData['type']);
        $this->assertSame(TestInput::class, $openData['context']['input']);
        $this->assertSame(['data' => 'data'], $openData['context']['prop']);
    }

    public function testCloseChainLogsSuccessExit(): void
    {
        $chainId = $this->logger->openChain(new TestInput('data'));
        $this->logger->closeChain(new FakeProcessedData('done'), $chainId);

        $logData = $this->semanticLogger->toArray();
        assert(is_array($logData['close']));
        $closeData = $logData['close'][0];
        assert(is_array($closeData) && is_array($closeData['context']));

        $this->assertSame('becoming_close', $closeData['type']);
        $this->assertSame('success', $closeData['context']['exit']);
        $this->assertSame(FakeProcessedData::class, $closeData['context']['final']);
    }

    public function testCloseChainLogsErrorExit(): void
    {
        $chainId = $this->logger->openChain(new TestInput('data'));
        $this->logger->closeChain(null, $chainId, new RuntimeException('chain failed'));

        $logData = $this->semanticLogger->toArray();
        assert(is_array($logData['close']));
        $closeData = $logData['close'][0];
        assert(is_array($closeData) && is_array($closeData['context']));

        $this->assertSame('becoming_close', $closeData['type']);
        $this->assertSame('error', $closeData['context']['exit']);
        $this->assertSame(RuntimeException::class, $closeData['context']['error']);
        $this->assertSame('chain failed', $closeData['context']['message']);
    }

    public function testComplexTransformationWithDependency(): void
    {
        $injector = new Injector();
        $input = new TestInputWithDependency('test data', $injector);

        $openId = $this->logger->open($input, FakeProcessedData::class, []);

        // FakeProcessedData has no further #[Be] → final close
        $result = new FakeProcessedData('processed');
        $this->logger->close($result, $openId);

        $logData = $this->semanticLogger->toArray();
        assert(is_array($logData['open']) && is_array($logData['close']));
        $openData = $logData['open'][0];
        $closeData = $logData['close'][0];

        $this->assertArrayHasKey('inject', $openData['context']);

        $this->assertEquals('being_final_close', $closeData['type']);
        $this->assertEquals(FakeProcessedData::class, $closeData['context']['final']);
    }

    public function testExtractTranscendentSourcesDirectly(): void
    {
        $reflection = new ReflectionClass($this->logger);
        $method = $reflection->getMethod('extractTranscendentSources');

        $args = [
            'data' => 'test data',
            'unionParam' => 'test-union-value',
        ];

        $result = $method->invoke($this->logger, $args, FakeProcessedData::class);

        // FakeProcessedData only has #[Input] parameters, so no transcendent sources expected
        $this->assertEquals([], $result);
    }

    public function testMultipleDestination(): void
    {
        $input = new TestInput('test data');
        $openId = $this->logger->open($input, FakeProcessedData::class, []);

        // TestMultipleDestination has #[Be([A, B])] — next step is "being" (continuing)
        $result = new TestMultipleDestination();
        $this->logger->close($result, $openId);

        $logData = $this->semanticLogger->toArray();
        assert(is_array($logData['close']));
        $closeData = $logData['close'][0];

        $this->assertEquals('being_close', $closeData['type']);
        $this->assertEquals(TestMultipleDestination::class, $closeData['context']['being']);
    }

    public function testSingleDestination(): void
    {
        $input = new TestInput('test data');
        $openId = $this->logger->open($input, FakeProcessedData::class, []);

        // TestSingleDestination has #[Be(FakeProcessedData::class)] — next step is "being"
        $result = new TestSingleDestination('test');
        $this->logger->close($result, $openId);

        $logData = $this->semanticLogger->toArray();
        assert(is_array($logData['close']));
        $closeData = $logData['close'][0];

        $this->assertEquals('being_close', $closeData['type']);
        $this->assertEquals(TestSingleDestination::class, $closeData['context']['being']);
    }

    public function testExtractTranscendentSourcesWithNoConstructor(): void
    {
        $reflection = new ReflectionClass($this->logger);
        $method = $reflection->getMethod('extractTranscendentSources');

        $args = ['data' => 'test'];
        $result = $method->invoke($this->logger, $args, NoConstructorClass::class);

        $this->assertEquals([], $result);
    }

    public function testExtractPropertiesDirectly(): void
    {
        $reflection = new ReflectionClass($this->logger);
        $method = $reflection->getMethod('extractProperties');

        $testObject = new stdClass();
        $testObject->prop1 = 'value1';
        $testObject->prop2 = 42;

        $result = $method->invoke($this->logger, $testObject);

        $expected = ['prop1' => 'value1', 'prop2' => 42];
        $this->assertEquals($expected, $result);
    }

    public function testExtractPropertiesWithUninitializedDeclaredProperty(): void
    {
        // Accept-pattern objects leave some typed public properties uninitialized;
        // extractProperties must surface them as null rather than throw or omit them,
        // and must ignore public static properties entirely.
        $reflection = new ReflectionClass($this->logger);
        $method = $reflection->getMethod('extractProperties');

        $acceptLike = new class {
            public static string $shared = 'class-level';
            public string $initialized = 'ready';
            public string $pending;
        };

        $result = $method->invoke($this->logger, $acceptLike);

        $this->assertArrayHasKey('pending', $result);
        $this->assertNull($result['pending']);
        $this->assertSame('ready', $result['initialized']);
        $this->assertArrayNotHasKey('shared', $result);
    }

    public function testExtractTranscendentSourcesWithInjectObject(): void
    {
        $reflection = new ReflectionClass($this->logger);
        $method = $reflection->getMethod('extractTranscendentSources');

        $injectedObject = new stdClass();
        $injectedObject->test = 'value';

        $args = [
            'data' => 'test data',
            'injectedObject' => $injectedObject,
        ];

        $result = $method->invoke($this->logger, $args, ClassWithInjectObject::class);

        $this->assertArrayHasKey('injectedObject', $result);
        $this->assertSame('stdClass', $result['injectedObject']);
        $this->assertArrayNotHasKey('missingParam', $result);
    }
}
