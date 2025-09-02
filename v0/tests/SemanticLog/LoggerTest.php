<?php

declare(strict_types=1);

namespace Be\Framework\SemanticLog;

use Be\Framework\Be;
use Be\Framework\BecomingArguments;
use Be\Framework\FakeProcessedData;
use Be\Framework\SemanticLog\Context\DestinationNotFound;
use Be\Framework\SemanticLog\Context\FinalDestination;
use Be\Framework\SemanticLog\Context\MultipleDestination;
use Be\Framework\SemanticLog\Context\SingleDestination;
use Be\Framework\TestInputWithDependency;
use Be\Framework\TestMultipleDestination;
use Be\Framework\TestSingleDestination;
use Koriym\SemanticLogger\SemanticLogger;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;
use ReflectionClass;
use stdClass;

use function get_class;

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
        $becomingArguments = new BecomingArguments($injector);

        $this->logger = new Logger(
            $this->semanticLogger,
            $becomingArguments,
        );
    }

    public function testSingleTransformationLogging(): void
    {
        $input = new TestInput('test data');

        // Test open logging
        $openId = $this->logger->open($input, FakeProcessedData::class);
        $this->assertNotEmpty($openId);

        // Simulate successful transformation result
        $result = new stdClass();
        $result->processedData = 'test result';
        $this->logger->close($result, $openId);

        // Verify semantic logger captured data
        $logData = $this->semanticLogger->toArray();
        $this->assertArrayHasKey('open', $logData);
        $this->assertArrayHasKey('close', $logData);

        // Verify open log structure
        $openData = $logData['open'];
        $this->assertEquals(TestInput::class, $openData['context']['fromClass']);
        $this->assertEquals('#[Be(Be\Framework\FakeProcessedData::class)]', $openData['context']['beAttribute']);

        // Verify close log structure
        $closeData = $logData['close'];
        $this->assertArrayHasKey('properties', $closeData['context']);
    }

    public function testArrayBecomingSkipped(): void
    {
        $input = new TestInput('test data');

        // Array becoming should return empty openId (skipped logging)
        $openId = $this->logger->open($input, ['Class1', 'Class2']);
        $this->assertEquals('', $openId);

        // No need to test close() with empty openId - it returns early
        // This tests the array becoming skip logic only
    }

    public function testErrorLogging(): void
    {
        $input = new TestInput('test data');

        $openId = $this->logger->open($input, FakeProcessedData::class);

        // Test error case with null result
        $this->logger->close(null, $openId, 'Test error message');

        $logData = $this->semanticLogger->toArray();
        $closeData = $logData['close'];
        $this->assertInstanceOf(DestinationNotFound::class, $closeData['context']['be']);
        $this->assertEquals('Test error message', $closeData['context']['be']->error);
    }

    public function testErrorLoggingWithoutMessage(): void
    {
        $input = new TestInput('test data');

        $openId = $this->logger->open($input, FakeProcessedData::class);

        // Test error case without error message (triggers 'Unknown error')
        $this->logger->close(null, $openId);

        $logData = $this->semanticLogger->toArray();
        $closeData = $logData['close'];
        $destinationNotFound = $closeData['context']['be'];
        $this->assertInstanceOf(DestinationNotFound::class, $destinationNotFound);
        $this->assertEquals('Unknown error', $destinationNotFound->error);
    }

    public function testEmptyOpenIdSkip(): void
    {
        // Test that close() with empty openId returns early without causing errors
        // This simulates the array becoming case where openId is empty

        // Should not throw exception or create any logs
        $this->logger->close(new stdClass(), '');

        // Test passes if no exception is thrown - early return works correctly
        $this->assertTrue(true);
    }

    public function testComplexTransformationWithDependency(): void
    {
        // Create input with dependency injection to test extractTranscendentSources
        $injector = new Injector();
        $input = new TestInputWithDependency('test data', $injector);

        $openId = $this->logger->open($input, FakeProcessedData::class);

        // Use FakeProcessedData result to test determineDestination as FinalDestination
        $result = new FakeProcessedData('processed');
        $this->logger->close($result, $openId);

        $logData = $this->semanticLogger->toArray();
        $openData = $logData['open'];
        $closeData = $logData['close'];

        // Verify transcendent sources are captured
        $this->assertArrayHasKey('transcendentSources', $openData['context']);

        // Verify destination determination
        $this->assertInstanceOf(FinalDestination::class, $closeData['context']['be']);
        $this->assertEquals(FakeProcessedData::class, $closeData['context']['be']->finalClass);
    }

    public function testExtractTranscendentSourcesDirectly(): void
    {
        // Test the private extractTranscendentSources method directly using reflection
        $reflection = new ReflectionClass($this->logger);
        $method = $reflection->getMethod('extractTranscendentSources');
        $method->setAccessible(true);

        // Create args with mixed types to test the is_object condition
        $injector = new Injector();
        $stdClassObj = new stdClass();
        $stdClassObj->prop = 'value';

        $args = [
            'stringParam' => 'test string',
            'intParam' => 42,
            'injector' => $injector,
            'stdClassObj' => $stdClassObj,
            'nullParam' => null,
            'arrayParam' => ['test'],
        ];

        $result = $method->invoke($this->logger, $args);

        // Only object parameters should be in transcendent sources
        $expected = [
            'injector' => Injector::class,
            'stdClassObj' => stdClass::class,
        ];

        $this->assertEquals($expected, $result);
    }

    public function testMultipleDestination(): void
    {
        $input = new TestInput('test data');
        $openId = $this->logger->open($input, FakeProcessedData::class);

        // Create result with multiple destinations
        $result = new TestMultipleDestination();
        $this->logger->close($result, $openId);

        $logData = $this->semanticLogger->toArray();
        $closeData = $logData['close'];

        // Debug: check what type of destination was actually created
        $actualType = get_class($closeData['context']['be']);
        $this->assertInstanceOf(MultipleDestination::class, $closeData['context']['be'], "Expected MultipleDestination, got: $actualType");
    }

    public function testSingleDestination(): void
    {
        $input = new TestInput('test data');
        $openId = $this->logger->open($input, FakeProcessedData::class);

        // Create result with single destination
        $result = new TestSingleDestination('test');
        $this->logger->close($result, $openId);

        $logData = $this->semanticLogger->toArray();
        $closeData = $logData['close'];

        $this->assertInstanceOf(SingleDestination::class, $closeData['context']['be']);
        $this->assertEquals(FakeProcessedData::class, $closeData['context']['be']->nextClass);
    }
}
