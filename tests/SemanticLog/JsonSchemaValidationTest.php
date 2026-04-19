<?php

declare(strict_types=1);

namespace Be\Framework\SemanticLog;

use Be\Framework\SemanticLog\Context\BecomingBeingContext;
use Be\Framework\SemanticLog\Context\BecomingErrorContext;
use Be\Framework\SemanticLog\Context\BecomingFinalContext;
use Be\Framework\SemanticLog\Context\BecomingOpenContext;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Validator;
use PHPUnit\Framework\TestCase;

use function array_map;
use function file_get_contents;
use function json_decode;
use function json_encode;

/**
 * Automated JSON Schema validation tests
 *
 * Validates that our semantic logging contexts produce output
 * that conforms to the actual JSON schemas defined in docs/schemas/
 */
final class JsonSchemaValidationTest extends TestCase
{
    private Validator $validator;
    private object $openSchema;
    private object $beingSchema;
    private object $finalSchema;
    private object $errorSchema;

    protected function setUp(): void
    {
        $this->validator = new Validator();

        $this->openSchema = json_decode(file_get_contents(__DIR__ . '/../../docs/schemas/becoming-open.json'));
        $this->beingSchema = json_decode(file_get_contents(__DIR__ . '/../../docs/schemas/becoming-being.json'));
        $this->finalSchema = json_decode(file_get_contents(__DIR__ . '/../../docs/schemas/becoming-final.json'));
        $this->errorSchema = json_decode(file_get_contents(__DIR__ . '/../../docs/schemas/becoming-error.json'));
    }

    public function testBecomingOpenContextValidatesAgainstSchema(): void
    {
        $context = new BecomingOpenContext(
            from: 'Be\Framework\Test\UserInput',
            be: 'Be\Framework\Test\ValidatedUser',
            input: [
                'email' => 'UserInput::email',
                'name' => 'UserInput::name',
            ],
            inject: [
                'validator' => 'ValidatorInterface',
                'logger' => 'LoggerInterface',
            ],
        );

        $contextData = json_decode(json_encode($context), false);

        $this->validator->validate($contextData, $this->openSchema, Constraint::CHECK_MODE_NORMAL);

        $this->assertTrue(
            $this->validator->isValid(),
            'BecomingOpenContext should validate. Errors: ' . json_encode($this->validator->getErrors()),
        );
    }

    public function testBecomingOpenContextWithPipeJoinedBeValidates(): void
    {
        $context = new BecomingOpenContext(
            from: 'Be\Framework\Test\ProcessingData',
            be: 'Be\Framework\Test\Success|Be\Framework\Test\Failure',
            input: ['data' => 'ProcessingData::data'],
            inject: [],
        );

        $contextData = json_decode(json_encode($context), false);
        $this->validator->validate($contextData, $this->openSchema, Constraint::CHECK_MODE_NORMAL);

        $this->assertTrue(
            $this->validator->isValid(),
            'Pipe-joined be should validate. Errors: ' . json_encode($this->validator->getErrors()),
        );
    }

    public function testBecomingOpenContextMinimalValidates(): void
    {
        $context = new BecomingOpenContext(
            from: 'Be\Framework\Test\SimpleInput',
            be: 'Be\Framework\Test\SimpleOutput',
        );

        $contextData = json_decode(json_encode($context), false);
        $this->validator->validate($contextData, $this->openSchema, Constraint::CHECK_MODE_NORMAL);

        $this->assertTrue(
            $this->validator->isValid(),
            'Minimal open context should validate. Errors: ' . json_encode($this->validator->getErrors()),
        );
    }

    public function testBecomingBeingContextValidates(): void
    {
        $context = new BecomingBeingContext(
            prop: [
                'email' => 'user@example.com',
                'validated' => true,
            ],
            being: 'Be\Framework\Test\NextTransformation',
        );

        $contextData = json_decode(json_encode($context), false);
        $this->validator->validate($contextData, $this->beingSchema, Constraint::CHECK_MODE_NORMAL);

        $this->assertTrue(
            $this->validator->isValid(),
            'BecomingBeingContext should validate. Errors: ' . json_encode($this->validator->getErrors()),
        );
    }

    public function testBecomingFinalContextValidates(): void
    {
        $context = new BecomingFinalContext(
            prop: [
                'result' => 'success',
                'data' => ['key' => 'value'],
            ],
            final: 'Be\Framework\Test\FinalClass',
        );

        $contextData = json_decode(json_encode($context), false);
        $this->validator->validate($contextData, $this->finalSchema, Constraint::CHECK_MODE_NORMAL);

        $this->assertTrue(
            $this->validator->isValid(),
            'BecomingFinalContext should validate. Errors: ' . json_encode($this->validator->getErrors()),
        );
    }

    public function testBecomingErrorContextValidates(): void
    {
        $context = new BecomingErrorContext(
            error: 'RuntimeException',
            message: 'Something went wrong',
        );

        $contextData = json_decode(json_encode($context), false);
        $this->validator->validate($contextData, $this->errorSchema, Constraint::CHECK_MODE_NORMAL);

        $this->assertTrue(
            $this->validator->isValid(),
            'BecomingErrorContext should validate. Errors: ' . json_encode($this->validator->getErrors()),
        );
    }

    public function testInvalidOpenContextFailsValidation(): void
    {
        $invalidData = (object) [
            'be' => 'SomeClass',
            // Missing required 'from'
        ];

        $this->validator->validate($invalidData, $this->openSchema, Constraint::CHECK_MODE_NORMAL);

        $this->assertFalse(
            $this->validator->isValid(),
            'Missing-required open should fail validation',
        );

        $errors = $this->validator->getErrors();
        $this->assertNotEmpty($errors);

        $errorMessages = array_map(static fn ($error) => $error['message'], $errors);
        $this->assertContains(
            'The property from is required',
            $errorMessages,
        );
    }
}
