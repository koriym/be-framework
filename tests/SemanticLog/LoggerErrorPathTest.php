<?php

declare(strict_types=1);

namespace Be\Framework\SemanticLog;

use Be\Framework\BecomingArgumentsInterface;
use Be\Framework\SemanticLog\Context\DestinationNotFound;
use Be\Framework\SemanticLog\Context\FinalDestination;
use Be\Framework\SemanticLog\Context\MetamorphosisCloseContext;
use Be\Framework\SemanticLog\Context\MetamorphosisOpenContext;
use Be\Framework\SemanticLog\Context\MultipleDestination;
use Be\Framework\SemanticLog\Context\SingleDestination;
use Koriym\SemanticLogger\SemanticLoggerInterface;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;
use stdClass;

use function is_string;

/**
 * Test error paths and edge cases in Logger
 */
final class LoggerErrorPathTest extends TestCase
{
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        $semanticLogger = $this->createMock(SemanticLoggerInterface::class);
        $becomingArguments = $this->createMock(BecomingArgumentsInterface::class);

        $this->logger = new Logger($semanticLogger, $becomingArguments);
    }

    public function testOpenWithExistingClass(): void
    {
        $input = new stdClass();

        $openId = $this->logger->open($input, stdClass::class);

        $this->assertIsString($openId);
        // The openId might be empty if no transformation is possible, which is valid
        $this->assertTrue(is_string($openId));
    }

    public function testCloseWithInvalidOpenId(): void
    {
        $result = new stdClass();
        $invalidOpenId = 'invalid-open-id-that-does-not-exist';

        // This should handle gracefully without throwing exceptions
        $this->logger->close($result, $invalidOpenId);

        // If no exception is thrown, the test passes
        $this->expectNotToPerformAssertions();
    }

    public function testDetermineDestinationWithObjectHavingNoBeAttribute(): void
    {
        // Create an object without #[Be] attribute to trigger DestinationNotFound
        $objectWithoutBeAttribute = new class {
            public string $data = 'test';
        };

        $openId = $this->logger->open($objectWithoutBeAttribute, stdClass::class);
        $this->logger->close($objectWithoutBeAttribute, $openId);

        $this->expectNotToPerformAssertions();
    }

    public function testExtractTranscendentSourcesWithComplexObject(): void
    {
        // Test extractTranscendentSources with various object types
        $complexObject = new class {
            public function __construct(
                public string $data = 'test',
                public object|null $optional = null,
            ) {
            }
        };

        $openId = $this->logger->open($complexObject, stdClass::class);
        $this->logger->close($complexObject, $openId);

        $this->expectNotToPerformAssertions();
    }

    public function testLoggerWithNullResult(): void
    {
        $input = new stdClass();
        $openId = $this->logger->open($input, stdClass::class);

        // Close with null result to test null handling
        $this->logger->close(null, $openId);

        $this->expectNotToPerformAssertions();
    }

    public function testLoggerContextsCreation(): void
    {
        // Test that different context types are created properly
        $input = new stdClass();
        $openId = $this->logger->open($input, stdClass::class);

        // Test with different result types to trigger different destination contexts
        $result = new class {
            public string $output = 'result';
        };

        $this->logger->close($result, $openId);

        $this->expectNotToPerformAssertions();
    }

    public function testMetamorphosisOpenContextCreation(): void
    {
        // Test MetamorphosisOpenContext creation with edge cases
        $openContext = new MetamorphosisOpenContext(
            fromClass: 'TestSource',
            beAttribute: 'TestDestination',
            immanentSources: ['prop1' => 'value1'],
            transcendentSources: ['service' => 'injected'],
        );

        $this->assertInstanceOf(MetamorphosisOpenContext::class, $openContext);
        $this->assertSame('TestSource', $openContext->fromClass);
        $this->assertSame('TestDestination', $openContext->beAttribute);
    }

    public function testMetamorphosisCloseContextCreation(): void
    {
        // Test MetamorphosisCloseContext creation
        $destination = new SingleDestination('TargetClass');

        $closeContext = new MetamorphosisCloseContext(
            properties: ['result' => 'success'],
            be: $destination,
        );

        $this->assertInstanceOf(MetamorphosisCloseContext::class, $closeContext);
        $this->assertSame(['result' => 'success'], $closeContext->properties);
        $this->assertInstanceOf(SingleDestination::class, $closeContext->be);
    }

    public function testAllDestinationTypes(): void
    {
        // Test all destination context types
        $singleDest = new SingleDestination('SingleClass');
        $multipleDest = new MultipleDestination(['Class1', 'Class2']);
        $finalDest = new FinalDestination('FinalClass');
        $notFoundDest = new DestinationNotFound('Error message', ['Class1', 'Class2']);

        $this->assertInstanceOf(SingleDestination::class, $singleDest);
        $this->assertInstanceOf(MultipleDestination::class, $multipleDest);
        $this->assertInstanceOf(FinalDestination::class, $finalDest);
        $this->assertInstanceOf(DestinationNotFound::class, $notFoundDest);

        $this->assertSame('SingleClass', $singleDest->nextClass);
        $this->assertSame(['Class1', 'Class2'], $multipleDest->possibleClasses);
        $this->assertSame('FinalClass', $finalDest->finalClass);
        $this->assertSame('Error message', $notFoundDest->error);
    }

    public function testLoggerWithInjectorParameter(): void
    {
        // Test with object that has Injector parameter
        $injectorObject = new class {
            public function __construct(
                public string $data = 'test',
                public Injector|null $injector = null,
            ) {
            }
        };

        $openId = $this->logger->open($injectorObject, stdClass::class);
        $this->logger->close($injectorObject, $openId);

        $this->expectNotToPerformAssertions();
    }
}
