<?php

declare(strict_types=1);

namespace Be\Framework\SemanticLog;

use Be\Framework\Be;
use Be\Framework\BecomingArguments;
use Be\Framework\ClassWithInjectObject;
use Be\Framework\FakeProcessedData;
use Be\Framework\NoConstructorClass;
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

    public function testArrayBecomingLogging(): void
    {
        $input = new TestInput('test data');

        // Array becoming should now return openId (logging enabled)
        $openId = $this->logger->open($input, ['Class1', 'Class2']);
        $this->assertNotEmpty($openId);

        // Close the session to complete the log
        $this->logger->close(new stdClass(), $openId);

        // Verify array becoming is logged correctly
        $logData = $this->semanticLogger->toArray();
        $openData = $logData['open'];
        $this->assertEquals('#[Be([Class1::class, Class2::class])]', $openData['context']['beAttribute']);
        $this->assertEquals([], $openData['context']['immanentSources']); // Empty for array case
        $this->assertEquals([], $openData['context']['transcendentSources']); // Empty for array case
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

        // Create args for a class with #[Inject] parameter (use BecomingTestWithUnion from main tests)
        $args = [
            'data' => 'test data', // #[Input] parameter - should not appear in transcendent sources
            'unionParam' => 'test-union-value', // #[Inject] parameter - should appear in transcendent sources
        ];

        // Use a simple class name that exists - let's use FakeProcessedData but expect no transcendent sources
        // since it only has #[Input] parameters
        $result = $method->invoke($this->logger, $args, FakeProcessedData::class);

        // FakeProcessedData only has #[Input] parameters, so no transcendent sources expected
        $expected = [];

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

    public function testExtractTranscendentSourcesWithNoConstructor(): void
    {
        // Test the case where target class has no constructor
        $reflection = new ReflectionClass($this->logger);
        $method = $reflection->getMethod('extractTranscendentSources');
        $method->setAccessible(true);

        $args = ['data' => 'test'];

        // Use NoConstructorClass which has no constructor
        $result = $method->invoke($this->logger, $args, NoConstructorClass::class);

        // Should return empty array when no constructor exists
        $this->assertEquals([], $result);
    }

    public function testExtractPropertiesDirectly(): void
    {
        // Test the private extractProperties method directly
        $reflection = new ReflectionClass($this->logger);
        $method = $reflection->getMethod('extractProperties');
        $method->setAccessible(true);

        $testObject = new stdClass();
        $testObject->prop1 = 'value1';
        $testObject->prop2 = 42;

        $result = $method->invoke($this->logger, $testObject);

        $expected = ['prop1' => 'value1', 'prop2' => 42];
        $this->assertEquals($expected, $result);
    }

    public function testExtractTranscendentSourcesWithInjectObject(): void
    {
        // Test uncovered lines: continue and object case
        $reflection = new ReflectionClass($this->logger);
        $method = $reflection->getMethod('extractTranscendentSources');
        $method->setAccessible(true);

        $injectedObject = new stdClass();
        $injectedObject->test = 'value';

        // Args with object injection (covers line 149) and missing param (covers line 140)
        $args = [
            'data' => 'test data',
            'injectedObject' => $injectedObject,
            // 'missingParam' is intentionally missing to trigger continue (line 140)
        ];

        $result = $method->invoke($this->logger, $args, ClassWithInjectObject::class);

        // Should contain the injected object class name
        $this->assertArrayHasKey('injectedObject', $result);
        $this->assertSame('stdClass', $result['injectedObject']);

        // Missing param should not be in result due to continue
        $this->assertArrayNotHasKey('missingParam', $result);
    }
}
