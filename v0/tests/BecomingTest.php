<?php

declare(strict_types=1);

namespace Be\Framework;

use Be\Framework\Attribute\Be;
use Be\Framework\Attribute\Validate;
use Be\Framework\Exception\ConflictingParameterAttributes;
use Be\Framework\Exception\MissingParameterAttribute;
use Be\Framework\Exception\SemanticVariableException;
use Be\Framework\Exception\TypeMatchingFailure;
use Be\Framework\SemanticVariable\Errors;
use Be\Framework\SemanticVariable\SemanticValidator;
use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Ray\Di\AbstractModule;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use Ray\Di\Injector;
use Ray\InputQuery\Attribute\Input;
use RuntimeException;

use function filter_var;

use const FILTER_VALIDATE_EMAIL;

final class BecomingTest extends TestCase
{
    private Becoming $becoming;

    protected function setUp(): void
    {
        $injector = new Injector(new BecomingTestModule());

        // BecomingArgumentsには必ずSemanticValidatorを渡す
        $semanticValidator = new SemanticValidator('MyVendor\\MyApp\\SemanticVariables');
        $becomingArguments = new BecomingArguments($injector, $semanticValidator);

        $this->becoming = new Becoming($injector, 'MyVendor\\MyApp', null, $becomingArguments);
    }

    public function testSingleTransformation(): void
    {
        $input = new BecomingTestInput('test data');
        $result = ($this->becoming)($input);

        $this->assertInstanceOf(BecomingTestProcessed::class, $result);
        $this->assertEquals('PROCESSED: test data', $result->processedData);
    }

    public function testChainedTransformation(): void
    {
        $input = new BecomingTestChainStart('hello');
        $result = ($this->becoming)($input);

        // Should go through: ChainStart -> ChainMiddle -> ChainEnd
        $this->assertInstanceOf(BecomingTestChainEnd::class, $result);
        $this->assertEquals('hello -> middle -> end', $result->finalValue);
    }

    public function testNoTransformation(): void
    {
        $input = new BecomingTestFinal('final value');
        $result = ($this->becoming)($input);

        // Should return the same object as it has no #[Be] attribute
        $this->assertSame($input, $result);
        $this->assertEquals('final value', $result->value);
    }

    public function testArrayTransformationWithTypeMatching(): void
    {
        // Test successful path
        $successInput = new BecomingTestBranching('success', 100);
        $result = ($this->becoming)($successInput);

        $this->assertInstanceOf(BecomingTestSuccessPath::class, $result);
        $this->assertEquals('success', $result->status);
        $this->assertEquals(100, $result->value);

        // Test failure path
        $failureInput = new BecomingTestBranching('failure', 50);
        $result = ($this->becoming)($failureInput);

        $this->assertInstanceOf(BecomingTestFailurePath::class, $result);
        $this->assertEquals('failure', $result->status);
        $this->assertEquals(50, $result->value);
    }

    public function testArrayTransformationWithNoMatch(): void
    {
        $this->expectException(TypeMatchingFailure::class);
        $this->expectExceptionMessage('No matching class for becoming in [Be\Framework\BecomingTestImpossible1, Be\Framework\BecomingTestImpossible2]');

        $input = new BecomingTestNoMatch('test');
        ($this->becoming)($input);
    }

    public function testTransformationWithEmptyConstructor(): void
    {
        $input = new BecomingTestWithEmpty('data');
        $result = ($this->becoming)($input);

        $this->assertInstanceOf(BecomingTestEmpty::class, $result);
    }

    public function testSingleTransformationException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Always throws exception');

        $input = new BecomingTestExceptionThrows('test');
        ($this->becoming)($input);
    }

    public function testMissingAttributeValidation(): void
    {
        $this->expectException(MissingParameterAttribute::class);
        $this->expectExceptionMessage('Parameter "data" in Be\Framework\BecomingTestMissingAttribute::__construct must have either #[Input] or #[Inject] attribute');

        $input = new BecomingTestMissingAttributeInput('test');
        ($this->becoming)($input);
    }

    public function testBothAttributesValidation(): void
    {
        $this->expectException(ConflictingParameterAttributes::class);
        $this->expectExceptionMessage('Parameter "data" in Be\Framework\BecomingTestBothAttributes::__construct cannot have both #[Input] and #[Inject] attributes simultaneously');

        $input = new BecomingTestBothAttributesInput('test');
        ($this->becoming)($input);
    }

    public function testInputParameterWithDefaultValue(): void
    {
        $input = new BecomingTestDefaultInput('test');
        $result = ($this->becoming)($input);

        $this->assertInstanceOf(BecomingTestWithDefault::class, $result);
        $this->assertEquals('test', $result->data);
        $this->assertEquals('default-value', $result->defaultParam);
    }

    public function testUnionTypeWithNamedBinding(): void
    {
        $input = new BecomingTestUnionInput('test');
        $result = ($this->becoming)($input);

        $this->assertInstanceOf(BecomingTestWithUnion::class, $result);
        $this->assertEquals('test', $result->data);
        $this->assertEquals('test-union-value', $result->unionParam);
    }

    public function testArrayTransformationWithTypeMatchAndConstructorFailure(): void
    {
        // Test the case where type matching succeeds but constructor fails
        // This should hit lines 106-107 in Being.php (candidateErrors recording)

        $input = new BecomingTestFailureInput('test');
        $result = ($this->becoming)($input);

        // Should successfully transform using the fallback target class
        $this->assertInstanceOf(BecomingTestSuccessfulFallback::class, $result);
        $this->assertEquals('test-processed', $result->processedValue);
    }

    public function testSemanticVariableExceptionIsNotRetried(): void
    {
        // Test line 113 in Being.php - SemanticVariableException should be re-thrown immediately
        $this->expectException(SemanticVariableException::class);

        $input = new BecomingTestSemanticFailureInput('invalid-email');
        ($this->becoming)($input);
    }

    public function testSemanticValidationFailure(): void
    {
        $this->expectException(SemanticVariableException::class);

        $input = new BecomingTestSemanticInvalid('invalid-email');
        ($this->becoming)($input);
    }

    public function testTypeMatchingFailureWithFallback(): void
    {
        // This test covers lines 106-107 in Being.php performTypeMatching
        // First candidate will fail type matching, second will succeed
        $input = new BecomingTestTypeMismatchInput('test-string', 42);
        $result = ($this->becoming)($input);

        // Should succeed with the compatible target class
        $this->assertInstanceOf(BecomingTestTypeCompatible::class, $result);
        $this->assertEquals('test-string', $result->stringValue);
        $this->assertEquals(42, $result->intValue);
    }
}

// Test fixtures for coverage testing

// Classes for testing failure scenarios
#[Be([BecomingTestFailingTarget::class, BecomingTestSuccessfulFallback::class])]
final class BecomingTestFailureInput
{
    public function __construct(
        public readonly string $value,
    ) {
    }
}

// This class will fail during construction when value = 'test'
final class BecomingTestFailingTarget
{
    public function __construct(
        #[Input]
        string $value,
    ) {
        if ($value === 'test') {
            throw new RuntimeException('Intentional constructor failure for coverage test');
        }

        $this->processedValue = $value;
    }

    public readonly string $processedValue;
}

// This class should succeed as fallback
final class BecomingTestSuccessfulFallback
{
    public function __construct(
        #[Input]
        string $value,
    ) {
        $this->processedValue = $value . '-processed';
    }

    public readonly string $processedValue;
}

// Class for testing SemanticVariableException re-throw
#[Be([BecomingTestSemanticFailingTarget::class])]
final class BecomingTestSemanticFailureInput
{
    public function __construct(
        public readonly string $email,
    ) {
    }
}

final class BecomingTestSemanticFailingTarget
{
    public function __construct(
        #[Input]
        string $email,
    ) {
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors = new Errors([
                'Invalid email format',
            ]);

            throw new SemanticVariableException($errors);
        }

        $this->processedEmail = $email;
    }

    public readonly string $processedEmail;
}

// Type mismatch test fixtures - for covering lines 106-107 in Being.php
#[Be([BecomingTestTypeIncompatible::class, BecomingTestTypeCompatible::class])]
final class BecomingTestTypeMismatchInput
{
    public function __construct(
        public readonly string $stringValue,
        public readonly int $intValue,
    ) {
    }
}

// This class expects int for stringValue - will cause type mismatch
final class BecomingTestTypeIncompatible
{
    public function __construct(
        #[Input]
        int $stringValue,  // Type mismatch: expects int but gets string
        #[Input]
        int $intValue,
    ) {
        $this->processedValue = $stringValue + $intValue;
    }

    public readonly int $processedValue;
}

// This class has compatible types - will succeed after first fails
final class BecomingTestTypeCompatible
{
    public function __construct(
        #[Input]
        public readonly string $stringValue,  // Compatible: expects string, gets string
        #[Input]
        public readonly int $intValue,        // Compatible: expects int, gets int
    ) {
    }
}

// Original test fixtures

#[Be(BecomingTestProcessed::class)]
final class BecomingTestInput
{
    public function __construct(
        public readonly string $data,
    ) {
    }
}

final class BecomingTestProcessed
{
    public function __construct(
        #[Input]
        string $data,
    ) {
        $this->processedData = 'PROCESSED: ' . $data;
    }

    public readonly string $processedData;
}

// Chain transformation fixtures
#[Be(BecomingTestChainMiddle::class)]
final class BecomingTestChainStart
{
    public function __construct(
        public readonly string $value,
    ) {
    }
}

#[Be(BecomingTestChainEnd::class)]
final class BecomingTestChainMiddle
{
    public function __construct(
        #[Input]
        string $value,
    ) {
        $this->intermediateValue = $value . ' -> middle';
    }

    public readonly string $intermediateValue;
}

final class BecomingTestChainEnd
{
    public function __construct(
        #[Input]
        string $intermediateValue,
    ) {
        $this->finalValue = $intermediateValue . ' -> end';
    }

    public readonly string $finalValue;
}

// Final object (no transformation)
final class BecomingTestFinal
{
    public function __construct(
        public readonly string $value,
    ) {
    }
}

// Branching transformation fixtures
#[Be([BecomingTestSuccessPath::class, BecomingTestFailurePath::class])]
final class BecomingTestBranching
{
    public function __construct(
        public readonly string $type,
        public readonly int $value,
    ) {
    }
}

final class BecomingTestSuccessPath
{
    public function __construct(
        #[Input]
        string $type,
        #[Input]
        public readonly int $value,
    ) {
        if ($type !== 'success' || $value < 100) {
            throw new InvalidArgumentException('Invalid success conditions');
        }

        $this->status = 'success';
    }

    public readonly string $status;
}

final class BecomingTestFailurePath
{
    public function __construct(
        #[Input]
        string $type,
        #[Input]
        public readonly int $value,
    ) {
        // Accepts any input that doesn't match success criteria
        $this->status = 'failure';
    }

    public readonly string $status;
}

// No match fixtures
#[Be([BecomingTestImpossible1::class, BecomingTestImpossible2::class])]
final class BecomingTestNoMatch
{
    public function __construct(
        public readonly string $data,
    ) {
    }
}

final class BecomingTestImpossible1
{
    public function __construct(
        #[Input]
        string $data,
    ) {
        throw new Exception('Always fails');
    }
}

final class BecomingTestImpossible2
{
    public function __construct(
        #[Input]
        string $data,
    ) {
        throw new Exception('Also always fails');
    }
}

// Empty constructor fixtures
#[Be(BecomingTestEmpty::class)]
final class BecomingTestWithEmpty
{
    public function __construct(
        public readonly string $data,
    ) {
    }
}

final class BecomingTestEmpty
{
    // No constructor - should be handled correctly
}

// Exception throwing fixture
#[Be(BecomingTestNeverReached::class)]
final class BecomingTestExceptionThrows
{
    public function __construct(
        public readonly string $data,
    ) {
    }
}

final class BecomingTestNeverReached
{
    public function __construct(
        #[Input]
        string $data,
    ) {
        throw new InvalidArgumentException('Always throws exception');
    }
}

// Missing attribute validation fixture
#[Be(BecomingTestMissingAttribute::class)]
final class BecomingTestMissingAttributeInput
{
    public function __construct(
        public readonly string $data,
    ) {
    }
}

final class BecomingTestMissingAttribute
{
    public function __construct(
        public readonly string $data,  // Missing #[Input] or #[Inject] attribute intentionally
    ) {
    }
}

// Both attributes validation fixture
#[Be(BecomingTestBothAttributes::class)]
final class BecomingTestBothAttributesInput
{
    public function __construct(
        public readonly string $data,
    ) {
    }
}

final class BecomingTestBothAttributes
{
    public function __construct(
        #[Input]
        #[Inject]
        public readonly string $data,  // Both attributes - should fail
    ) {
    }
}

// Default parameter value fixture
#[Be(BecomingTestWithDefault::class)]
final class BecomingTestDefaultInput
{
    public function __construct(
        public readonly string $data,
        public readonly string $defaultParam = 'default-value',
    ) {
    }
}

final class BecomingTestWithDefault
{
    public function __construct(
        #[Input]
        public readonly string $data,
        #[Input]
        public readonly string $defaultParam = 'default-value',
    ) {
    }
}

// Union type fixture
#[Be(BecomingTestWithUnion::class)]
final class BecomingTestUnionInput
{
    public function __construct(
        public readonly string $data,
    ) {
    }
}

final class BecomingTestWithUnion
{
    public function __construct(
        #[Input]
        public readonly string $data,
        #[Inject]
        #[Named('unionValue')]
        public readonly string|int $unionParam,
    ) {
    }
}

final class BecomingTestModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->bind('')->annotatedWith('unionValue')->toInstance('test-union-value');
    }
}

// Semantic validation test fixtures
#[Be(BecomingTestSemanticTarget::class)]
final class BecomingTestSemanticInvalid
{
    public function __construct(
        public readonly string $email,
    ) {
    }
}

final class BecomingTestSemanticTarget
{
    public function __construct(
        #[Input]
        #[Validate('Email')]
        public readonly string $email,  // This will trigger semantic validation for email
    ) {
    }
}
