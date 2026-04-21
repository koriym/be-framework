<?php

declare(strict_types=1);

namespace Be\Framework\SemanticLog;

use Be\Framework\SemanticLog\Context\BeingCloseContext;
use Be\Framework\SemanticLog\Context\BeingErrorCloseContext;
use Be\Framework\SemanticLog\Context\BeingFinalCloseContext;
use Be\Framework\SemanticLog\Context\BeingFinalOpenContext;
use Be\Framework\SemanticLog\Context\BeingOpenContext;
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
    private object $finalOpenSchema;
    private object $closeSchema;
    private object $finalCloseSchema;
    private object $errorCloseSchema;

    protected function setUp(): void
    {
        $this->validator = new Validator();

        $this->openSchema = json_decode(file_get_contents(__DIR__ . '/../../docs/schemas/being-open.json'));
        $this->finalOpenSchema = json_decode(file_get_contents(__DIR__ . '/../../docs/schemas/being-final-open.json'));
        $this->closeSchema = json_decode(file_get_contents(__DIR__ . '/../../docs/schemas/being-close.json'));
        $this->finalCloseSchema = json_decode(file_get_contents(__DIR__ . '/../../docs/schemas/being-final-close.json'));
        $this->errorCloseSchema = json_decode(file_get_contents(__DIR__ . '/../../docs/schemas/being-error-close.json'));
    }

    public function testBeingOpenContextValidatesAgainstSchema(): void
    {
        $context = new BeingOpenContext(
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
            'BeingOpenContext should validate. Errors: ' . json_encode($this->validator->getErrors()),
        );
    }

    public function testBeingFinalOpenContextValidatesAgainstSchema(): void
    {
        $context = new BeingFinalOpenContext(
            from: 'Be\Framework\Test\ProcessingData',
            be: 'Be\Framework\Test\TerminalResult',
            input: ['data' => 'ProcessingData::data'],
            inject: [],
        );

        $contextData = json_decode(json_encode($context), false);
        $this->validator->validate($contextData, $this->finalOpenSchema, Constraint::CHECK_MODE_NORMAL);

        $this->assertTrue(
            $this->validator->isValid(),
            'BeingFinalOpenContext should validate. Errors: ' . json_encode($this->validator->getErrors()),
        );
    }

    public function testBeingOpenContextMinimalValidates(): void
    {
        $context = new BeingOpenContext(
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

    public function testBeingCloseContextValidates(): void
    {
        $context = new BeingCloseContext(
            prop: [
                'email' => 'user@example.com',
                'validated' => true,
            ],
            being: 'Be\Framework\Test\NextTransformation',
        );

        $contextData = json_decode(json_encode($context), false);
        $this->validator->validate($contextData, $this->closeSchema, Constraint::CHECK_MODE_NORMAL);

        $this->assertTrue(
            $this->validator->isValid(),
            'BeingCloseContext should validate. Errors: ' . json_encode($this->validator->getErrors()),
        );
    }

    public function testBeingFinalCloseContextValidates(): void
    {
        $context = new BeingFinalCloseContext(
            prop: [
                'result' => 'success',
                'data' => ['key' => 'value'],
            ],
            final: 'Be\Framework\Test\FinalClass',
        );

        $contextData = json_decode(json_encode($context), false);
        $this->validator->validate($contextData, $this->finalCloseSchema, Constraint::CHECK_MODE_NORMAL);

        $this->assertTrue(
            $this->validator->isValid(),
            'BeingFinalCloseContext should validate. Errors: ' . json_encode($this->validator->getErrors()),
        );
    }

    public function testBeingErrorCloseContextValidates(): void
    {
        $context = new BeingErrorCloseContext(
            error: 'RuntimeException',
            message: 'Something went wrong',
        );

        $contextData = json_decode(json_encode($context), false);
        $this->validator->validate($contextData, $this->errorCloseSchema, Constraint::CHECK_MODE_NORMAL);

        $this->assertTrue(
            $this->validator->isValid(),
            'BeingErrorCloseContext should validate. Errors: ' . json_encode($this->validator->getErrors()),
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
