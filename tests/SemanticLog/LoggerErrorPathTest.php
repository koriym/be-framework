<?php

declare(strict_types=1);

namespace Be\Framework\SemanticLog;

use Be\Framework\BecomingArgumentsInterface;
use Be\Framework\SemanticLog\Context\BecomingBeingContext;
use Be\Framework\SemanticLog\Context\BecomingErrorContext;
use Be\Framework\SemanticLog\Context\BecomingFinalContext;
use Be\Framework\SemanticLog\Context\BecomingOpenContext;
use Koriym\SemanticLogger\SemanticLoggerInterface;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;
use RuntimeException;
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
        $this->assertTrue(is_string($openId));
    }

    public function testCloseWithInvalidOpenId(): void
    {
        $result = new stdClass();
        $invalidOpenId = 'invalid-open-id-that-does-not-exist';

        // This should handle gracefully without throwing exceptions
        $this->logger->close($result, $invalidOpenId);

        $this->expectNotToPerformAssertions();
    }

    public function testDetermineDestinationWithObjectHavingNoBeAttribute(): void
    {
        $objectWithoutBeAttribute = new class {
            public string $data = 'test';
        };

        $openId = $this->logger->open($objectWithoutBeAttribute, stdClass::class);
        $this->logger->close($objectWithoutBeAttribute, $openId);

        $this->expectNotToPerformAssertions();
    }

    public function testExtractTranscendentSourcesWithComplexObject(): void
    {
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

        // Close with null result (no exception) — legacy path, still closes
        $this->logger->close(null, $openId);

        $this->expectNotToPerformAssertions();
    }

    public function testLoggerWithException(): void
    {
        // Use a real SemanticLogger so we can inspect the emitted becoming_error payload.
        $semanticLogger = new \Koriym\SemanticLogger\SemanticLogger();
        $becomingArguments = $this->createMock(BecomingArgumentsInterface::class);
        $logger = new Logger($semanticLogger, $becomingArguments);

        $input = new stdClass();
        $openId = $logger->open($input, stdClass::class);

        $logger->close(null, $openId, new RuntimeException('boom'));

        $logData = $semanticLogger->toArray();
        $this->assertSame('becoming_error', $logData['close']['type']);
        $this->assertSame(RuntimeException::class, $logData['close']['context']['error']);
        $this->assertSame('boom', $logData['close']['context']['message']);
    }

    public function testLoggerContextsCreation(): void
    {
        $input = new stdClass();
        $openId = $this->logger->open($input, stdClass::class);

        $result = new class {
            public string $output = 'result';
        };

        $this->logger->close($result, $openId);

        $this->expectNotToPerformAssertions();
    }

    public function testBecomingOpenContextCreation(): void
    {
        $openContext = new BecomingOpenContext(
            from: 'TestSource',
            be: 'TestDestination',
            input: ['prop1' => 'value1'],
            inject: ['service' => 'injected'],
        );

        $this->assertSame('TestSource', $openContext->from);
        $this->assertSame('TestDestination', $openContext->be);
        $this->assertSame(['prop1' => 'value1'], $openContext->input);
        $this->assertSame(['service' => 'injected'], $openContext->inject);
    }

    public function testBecomingBeingContextCreation(): void
    {
        $ctx = new BecomingBeingContext(
            prop: ['result' => 'success'],
            being: 'TargetClass',
        );

        $this->assertSame(['result' => 'success'], $ctx->prop);
        $this->assertSame('TargetClass', $ctx->being);
    }

    public function testBecomingFinalContextCreation(): void
    {
        $ctx = new BecomingFinalContext(
            prop: ['value' => 42],
            final: 'TerminalClass',
        );

        $this->assertSame(['value' => 42], $ctx->prop);
        $this->assertSame('TerminalClass', $ctx->final);
    }

    public function testBecomingErrorContextCreation(): void
    {
        $ctx = new BecomingErrorContext(
            error: 'RuntimeException',
            message: 'boom',
        );

        $this->assertSame('RuntimeException', $ctx->error);
        $this->assertSame('boom', $ctx->message);
    }

    public function testLoggerWithInjectorParameter(): void
    {
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
