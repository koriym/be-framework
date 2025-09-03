<?php

declare(strict_types=1);

namespace Be\Framework\SemanticLog;

use Be\Framework\SemanticLog\Context\FinalDestination;
use Be\Framework\SemanticLog\Context\MetamorphosisCloseContext;
use Be\Framework\SemanticLog\Context\MetamorphosisOpenContext;
use Be\Framework\SemanticLog\Context\SingleDestination;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Validator;
use PHPUnit\Framework\TestCase;

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
    private object $closeSchema;

    protected function setUp(): void
    {
        $this->validator = new Validator();
        
        // Load actual JSON schemas from files
        $openSchemaContent = file_get_contents(__DIR__ . '/../../docs/schemas/metamorphosis-open.json');
        $closeSchemaContent = file_get_contents(__DIR__ . '/../../docs/schemas/metamorphosis-close.json');
        
        $this->openSchema = json_decode($openSchemaContent);
        $this->closeSchema = json_decode($closeSchemaContent);
    }

    public function testMetamorphosisOpenContextValidatesAgainstSchema(): void
    {
        // Create a context with all possible fields
        $context = new MetamorphosisOpenContext(
            fromClass: 'Be\Framework\Test\UserInput',
            beAttribute: '#[Be(ValidatedUser::class)]',
            immanentSources: [
                'email' => 'UserInput::email',
                'name' => 'UserInput::name',
            ],
            transcendentSources: [
                'validator' => 'ValidatorInterface',
                'logger' => 'LoggerInterface',
            ]
        );

        // Convert context to JSON-compatible format
        $contextData = json_decode(json_encode($context), false);
        
        // Ensure empty arrays are treated as objects for schema validation
        if (empty($contextData->immanentSources)) {
            $contextData->immanentSources = new \stdClass();
        }
        if (empty($contextData->transcendentSources)) {
            $contextData->transcendentSources = new \stdClass();
        }

        // Validate against schema
        $this->validator->validate($contextData, $this->openSchema, Constraint::CHECK_MODE_NORMAL);

        $this->assertTrue(
            $this->validator->isValid(),
            'MetamorphosisOpenContext should validate against schema. Errors: ' . json_encode($this->validator->getErrors())
        );
    }

    public function testMetamorphosisOpenContextWithArrayBeAttributeValidates(): void
    {
        // Test with array of possible transformations
        $context = new MetamorphosisOpenContext(
            fromClass: 'Be\Framework\Test\ProcessingData',
            beAttribute: '#[Be([Success::class, Failure::class])]',
            immanentSources: ['data' => 'ProcessingData::data'],
            transcendentSources: []
        );

        $contextData = json_decode(json_encode($context), false);
        $this->validator->validate($contextData, $this->openSchema, Constraint::CHECK_MODE_NORMAL);

        $this->assertTrue(
            $this->validator->isValid(),
            'Array Be attribute should validate. Errors: ' . json_encode($this->validator->getErrors())
        );
    }

    public function testMetamorphosisOpenContextMinimalValidates(): void
    {
        // Test with only required fields
        $context = new MetamorphosisOpenContext(
            fromClass: 'Be\Framework\Test\SimpleInput',
            beAttribute: '#[Be(SimpleOutput::class)]'
        );

        $contextData = json_decode(json_encode($context), false);
        $this->validator->validate($contextData, $this->openSchema, Constraint::CHECK_MODE_NORMAL);

        $this->assertTrue(
            $this->validator->isValid(),
            'Minimal context should validate. Errors: ' . json_encode($this->validator->getErrors())
        );
    }

    public function testMetamorphosisCloseContextWithSingleDestinationValidates(): void
    {
        $context = new MetamorphosisCloseContext(
            properties: [
                'email' => 'user@example.com',
                'validated' => true,
            ],
            be: new SingleDestination('NextTransformation')
        );

        $contextData = json_decode(json_encode($context), false);
        $this->validator->validate($contextData, $this->closeSchema, Constraint::CHECK_MODE_NORMAL);

        $this->assertTrue(
            $this->validator->isValid(),
            'Close context with SingleDestination should validate. Errors: ' . json_encode($this->validator->getErrors())
        );
    }

    public function testMetamorphosisCloseContextWithFinalDestinationValidates(): void
    {
        $context = new MetamorphosisCloseContext(
            properties: [
                'result' => 'success',
                'data' => ['key' => 'value'],
            ],
            be: new FinalDestination('FinalClass')
        );

        $contextData = json_decode(json_encode($context), false);
        $this->validator->validate($contextData, $this->closeSchema, Constraint::CHECK_MODE_NORMAL);

        $this->assertTrue(
            $this->validator->isValid(),
            'Close context with FinalDestination should validate. Errors: ' . json_encode($this->validator->getErrors())
        );
    }

    public function testInvalidContextFailsValidation(): void
    {
        // Create invalid context (missing required field)
        $invalidData = (object) [
            'beAttribute' => '#[Be(SomeClass::class)]',
            // Missing required 'fromClass'
        ];

        $this->validator->validate($invalidData, $this->openSchema, Constraint::CHECK_MODE_NORMAL);

        $this->assertFalse(
            $this->validator->isValid(),
            'Invalid context should fail validation'
        );

        $errors = $this->validator->getErrors();
        $this->assertNotEmpty($errors, 'Should have validation errors');
        
        // Check that the error is about missing required field
        $errorMessages = array_map(fn($error) => $error['message'], $errors);
        $this->assertContains(
            'The property fromClass is required',
            $errorMessages,
            'Should report missing required field'
        );
    }

    public function testInvalidClassNamePatternFailsValidation(): void
    {
        // Create context with invalid class name pattern
        $invalidData = (object) [
            'fromClass' => '123InvalidClassName', // Class names can't start with numbers
            'beAttribute' => '#[Be(ValidClass::class)]',
        ];

        $this->validator->validate($invalidData, $this->openSchema, Constraint::CHECK_MODE_NORMAL);

        $this->assertFalse(
            $this->validator->isValid(),
            'Invalid class name pattern should fail validation'
        );
    }
}