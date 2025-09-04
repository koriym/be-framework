<?php

declare(strict_types=1);

namespace Be\Framework;

use Be\Framework\Attribute\Be;
use Be\Framework\Exception\ConflictingParameterAttributes;
use Be\Framework\Exception\MissingParameterAttribute;
use Be\Framework\Exception\SemanticVariableException;
use Be\Framework\Exception\TypeMatchingFailure;
use Be\Framework\SemanticVariable\NullValidator;
use Be\Framework\SemanticVariable\SemanticValidator;
use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Ray\Di\AbstractModule;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use Ray\Di\Injector;
use Ray\InputQuery\Attribute\Input;

final class BecomingTest extends TestCase
{
    private Becoming $becoming;

    protected function setUp(): void
    {
        $injector = new Injector(new BecomingTestModule());

        // BecomingArgumentsには必ずSemanticValidatorを渡す
        $nullValidator = new NullValidator();
        $tempBecomingArgs = new BecomingArguments($injector, $nullValidator);
        $semanticValidator = new SemanticValidator($tempBecomingArgs, 'Be\\Framework\\SemanticVariables');
        $becomingArguments = new BecomingArguments($injector, $semanticValidator);

        $this->becoming = new Becoming($injector, null, $becomingArguments);
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

    public function testSemanticValidationFailure(): void
    {
        $this->expectException(SemanticVariableException::class);

        $input = new BecomingTestSemanticInvalid('invalid-email');
        ($this->becoming)($input);
    }
}

// Test fixtures

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
        public readonly string $email,  // This will trigger semantic validation for email
    ) {
    }
}
