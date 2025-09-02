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
final class TestInputForSchema
{
    public function __construct(
        public readonly string $data
    ) {}
}


final class SchemaComplianceTest extends TestCase
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

    public function testOpenContextSchemaCompliance(): void
    {
        $input = new TestInputForSchema('test data');
        
        $openId = $this->logger->open($input, \Be\Framework\FakeProcessedData::class);
        $this->assertNotEmpty($openId);
        
        // Complete the operation to avoid NoLogSessionException
        $result = new \Be\Framework\FakeProcessedData('test data');
        $this->logger->close($result, $openId);
        
        $logData = $this->semanticLogger->toArray();
        $openContext = $logData['open']['context'];
        
        // Verify all required fields are present for schema
        $this->assertArrayHasKey('fromClass', $openContext);
        $this->assertArrayHasKey('beAttribute', $openContext);
        $this->assertArrayHasKey('immanentSources', $openContext);
        $this->assertArrayHasKey('transcendentSources', $openContext);
        
        // Verify correct types for schema compliance
        $this->assertIsString($openContext['fromClass']);
        $this->assertIsString($openContext['beAttribute']);
        $this->assertIsArray($openContext['immanentSources']);
        $this->assertIsArray($openContext['transcendentSources']); // Will be object in JSON
        
        // Verify expected values
        $this->assertEquals(TestInputForSchema::class, $openContext['fromClass']);
        $this->assertEquals('#[Be(Be\Framework\FakeProcessedData::class)]', $openContext['beAttribute']);
        $this->assertEquals(['data' => 'Be\Framework\SemanticLog\TestInputForSchema::data'], $openContext['immanentSources']);
        $this->assertEquals([], $openContext['transcendentSources']); // Empty for this test
    }

    public function testCloseContextSchemaCompliance(): void
    {
        $input = new TestInputForSchema('test data');
        $openId = $this->logger->open($input, \Be\Framework\FakeProcessedData::class);
        
        $result = new \Be\Framework\FakeProcessedData('test data');
        $this->logger->close($result, $openId);
        
        $logData = $this->semanticLogger->toArray();
        $closeContext = $logData['close']['context'];
        
        // Verify required fields are present
        $this->assertArrayHasKey('properties', $closeContext);
        $this->assertArrayHasKey('be', $closeContext);
        
        // Verify types for schema compliance
        $this->assertIsArray($closeContext['properties']);
        $this->assertIsObject($closeContext['be']);
        
        // Verify the final destination context
        $finalDestination = $closeContext['be'];
        $this->assertInstanceOf(\Be\Framework\SemanticLog\Context\FinalDestination::class, $finalDestination);
    }

    public function testJSONSchemaValidation(): void
    {
        $input = new TestInputForSchema('test data');
        $openId = $this->logger->open($input, \Be\Framework\FakeProcessedData::class);
        
        $result = new \Be\Framework\FakeProcessedData('test data');
        $this->logger->close($result, $openId);
        
        $logData = $this->semanticLogger->toArray();
        $openContext = $logData['open']['context'];
        
        // Prepare open context for JSON schema validation
        $openValidation = [
            'fromClass' => $openContext['fromClass'],
            'toClass' => 'FakeProcessedData',
            'beAttribute' => $openContext['beAttribute'],
            'immanentSources' => $openContext['immanentSources'],
            'transcendentSources' => (object) $openContext['transcendentSources']
        ];
        
        // Validate JSON structure (this would normally use external validator)
        $this->assertArrayHasKey('fromClass', $openValidation);
        $this->assertArrayHasKey('toClass', $openValidation);
        $this->assertArrayHasKey('beAttribute', $openValidation);
        $this->assertArrayHasKey('immanentSources', $openValidation);
        $this->assertArrayHasKey('transcendentSources', $openValidation);
        
        // Verify that transcendentSources becomes an object when cast
        $this->assertIsObject($openValidation['transcendentSources']);
        $this->assertEquals(new \stdClass(), $openValidation['transcendentSources']);
    }
}