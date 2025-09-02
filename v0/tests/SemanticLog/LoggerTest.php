<?php

declare(strict_types=1);

namespace Be\Framework\SemanticLog;

use Be\Framework\BecomingArguments;
use Be\Framework\Be;
use Ray\InputQuery\Attribute\Input;
use Koriym\SemanticLogger\SemanticLogger;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

#[Be(\Be\Framework\FakeProcessedData::class)]
final class TestInput
{
    public function __construct(
        public readonly string $data
    ) {}
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
            $becomingArguments
        );
    }

    public function testSingleTransformationLogging(): void
    {
        $input = new TestInput('test data');
        
        // Test open logging
        $openId = $this->logger->open($input, \Be\Framework\FakeProcessedData::class);
        $this->assertNotEmpty($openId);
        
        // Simulate successful transformation result  
        $result = new \stdClass();
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
        
        $openId = $this->logger->open($input, \Be\Framework\FakeProcessedData::class);
        
        // Test error case with null result
        $this->logger->close(null, $openId, 'Test error message');
        
        $logData = $this->semanticLogger->toArray();
        $closeData = $logData['close'];
        $this->assertInstanceOf(\Be\Framework\SemanticLog\Context\DestinationNotFound::class, $closeData['context']['be']);
        $this->assertEquals('Test error message', $closeData['context']['be']->error);
    }

    public function testErrorLoggingWithoutMessage(): void
    {
        $input = new TestInput('test data');
        
        $openId = $this->logger->open($input, \Be\Framework\FakeProcessedData::class);
        
        // Test error case without error message (triggers 'Unknown error')
        $this->logger->close(null, $openId);
        
        $logData = $this->semanticLogger->toArray();
        $closeData = $logData['close'];
        $destinationNotFound = $closeData['context']['be'];
        $this->assertInstanceOf(\Be\Framework\SemanticLog\Context\DestinationNotFound::class, $destinationNotFound);
        $this->assertEquals('Unknown error', $destinationNotFound->error);
    }

    public function testEmptyOpenIdSkip(): void
    {
        // Test that close() with empty openId returns early without causing errors
        // This simulates the array becoming case where openId is empty
        
        // Should not throw exception or create any logs
        $this->logger->close(new \stdClass(), '');
        
        // Test passes if no exception is thrown - early return works correctly
        $this->assertTrue(true);
    }
}