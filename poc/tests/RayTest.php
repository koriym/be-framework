<?php

declare(strict_types=1);

namespace Ray\Framework\Tests;

use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Ray\Di\AbstractModule;
use Ray\Di\Injector;
use Ray\Framework\BecomingArguments;
use Ray\Framework\Exception\TypeMatchingFailure;
use Ray\Framework\FakeActiveUser;
use Ray\Framework\FakeBranchingInput;
use Ray\Framework\FakeFailingBranch;
use Ray\Framework\FakeFinishedProcess;
use Ray\Framework\FakeInputData;
use Ray\Framework\FakeInvalidParameter;
use Ray\Framework\FakeNoMetamorphosis;
use Ray\Framework\FakePremiumUser;
use Ray\Framework\FakeRegularUser;
use Ray\Framework\FakeResult;
use Ray\Framework\FakeService;
use Ray\Framework\FakeUserInput;
use Ray\Framework\FakeValidatedUser;
use Ray\Framework\FakeWithInject;
use Ray\Framework\FakeWithNamed;
use Ray\Framework\Ray;
use Throwable;

final class RayTest extends TestCase
{
    private Ray $ray;

    protected function setUp(): void
    {
        $injector = new Injector();
        $this->ray = new Ray($injector);
    }

    public function testLinearMetamorphosis(): void
    {
        // ValidatedUser -> RegisteredUser -> ActiveUser
        $input = new FakeValidatedUser('John', 'john@example.com', 25);
        $result = ($this->ray)($input);

        $this->assertInstanceOf(FakeActiveUser::class, $result);
        $this->assertSame('John', $result->name);
        $this->assertSame('john@example.com', $result->email);
        $this->assertSame(25, $result->age);
        $this->assertNotNull($result->id);
        $this->assertInstanceOf(DateTimeImmutable::class, $result->activatedAt);
    }

    public function testBranchingMetamorphosisToPremiumUser(): void
    {
        // BranchingInput -> PremiumUser (when isPremium = true)
        $input = new FakeBranchingInput('Premium John', 'premium@example.com', true);
        $result = ($this->ray)($input);

        $this->assertInstanceOf(FakePremiumUser::class, $result);
        $this->assertSame('Premium John', $result->name);
        $this->assertSame('premium@example.com', $result->email);
        $this->assertTrue($result->isPremium);
    }

    public function testBranchingMetamorphosisToRegularUser(): void
    {
        // BranchingInput -> RegularUser (when isPremium = false, PremiumUser fails)
        $input = new FakeBranchingInput('Regular John', 'regular@example.com', false);
        $result = ($this->ray)($input);

        $this->assertInstanceOf(FakeRegularUser::class, $result);
        $this->assertSame('Regular John', $result->name);
        $this->assertSame('regular@example.com', $result->email);
        $this->assertFalse($result->isPremium);
    }

    public function testNoMetamorphosis(): void
    {
        // NoMetamorphosis (no #[Be] attribute)
        $input = new FakeNoMetamorphosis('Hello World');
        $result = ($this->ray)($input);

        $this->assertInstanceOf(FakeNoMetamorphosis::class, $result);
        $this->assertSame($input, $result); // Same instance returned
        $this->assertSame('Hello World', $result->message);
    }

    public function testValidationFailure(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Name cannot be empty');

        // This should fail during ValidatedUser construction
        new FakeValidatedUser('', 'test@example.com', 25);
    }

    public function testEmailValidationFailure(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email format');

        // This should fail during ValidatedUser construction
        new FakeValidatedUser('John', 'invalid-email', 25);
    }

    public function testAgeValidationFailure(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Age must be positive');

        // This should fail during ValidatedUser construction
        new FakeValidatedUser('John', 'john@example.com', -1);
    }

    public function testDirectUserInputWithoutValidation(): void
    {
        // UserInput has no #[Be] attribute, so no metamorphosis
        $input = new FakeUserInput('Direct User', 'direct@example.com', 30);
        $result = ($this->ray)($input);

        $this->assertInstanceOf(FakeUserInput::class, $result);
        $this->assertSame($input, $result); // Same instance returned
        $this->assertSame('Direct User', $result->name);
        $this->assertSame('direct@example.com', $result->email);
        $this->assertSame(30, $result->age);
    }

    public function testMetamorphosisChainPreservesProperties(): void
    {
        // Test that properties are preserved through the metamorphosis chain
        $input = new FakeValidatedUser('Chain Test', 'chain@example.com', 35);
        $result = ($this->ray)($input);

        $this->assertInstanceOf(FakeActiveUser::class, $result);
        $this->assertSame('Chain Test', $result->name);
        $this->assertSame('chain@example.com', $result->email);
        $this->assertSame(35, $result->age);

        // Properties added during metamorphosis
        $this->assertNotNull($result->id);
        $this->assertStringStartsWith('user_', $result->id);
        $this->assertInstanceOf(DateTimeImmutable::class, $result->activatedAt);
    }

    public function testTypeMatchingFailure(): void
    {
        $this->expectException(TypeMatchingFailure::class);
        $this->expectExceptionMessage('No matching class for becoming in [Ray\Framework\FakeFailingUserA, Ray\Framework\FakeFailingUserB]');

        // This should fail because both FakeFailingUserA and FakeFailingUserB require parameters that are not provided
        $input = new FakeFailingBranch('Test User');
        ($this->ray)($input);
    }

    public function testObjectPropertyInheritance(): void
    {
        // Test that object properties are passed to next transformation
        // FakeInputData -> FakeProcessingStep -> FakeFinishedProcess
        $input = new FakeInputData('hello world');
        $result = ($this->ray)($input);

        $this->assertInstanceOf(FakeFinishedProcess::class, $result);

        // Check that original input is preserved
        $this->assertSame('hello world', $result->input);

        // Check that result object is properly inherited
        $this->assertInstanceOf(FakeResult::class, $result->result);
        $this->assertSame('hello world', $result->result->value);
        $this->assertTrue($result->result->isSuccess); // length > 3
    }

    public function testObjectPropertyInheritanceWithFailure(): void
    {
        // Test with short input that should result in failure
        $input = new FakeInputData('hi');
        $result = ($this->ray)($input);

        $this->assertInstanceOf(FakeFinishedProcess::class, $result);

        // Check that original input is preserved
        $this->assertSame('hi', $result->input);

        // Check that result object shows failure
        $this->assertInstanceOf(FakeResult::class, $result->result);
        $this->assertSame('hi', $result->result->value);
        $this->assertFalse($result->result->isSuccess); // length <= 3
    }

    public function testAttributeValidationFailure(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must have either #[Input] or #[Inject] attribute');

        // This should fail because FakeInvalidParameter has no attributes
        $injector = new Injector();
        $args = new BecomingArguments($injector);
        ($args)(new FakeInputData('test'), FakeInvalidParameter::class);
    }

    public function testInjectWithDI(): void
    {
        // Setup DI binding using module
        $injector = new Injector(new class extends AbstractModule {
            protected function configure(): void
            {
                $this->bind(FakeService::class)->toInstance(new FakeService('CustomService'));
            }
        });

        $args = new BecomingArguments($injector);
        $result = ($args)(new FakeInputData('test'), FakeWithInject::class);

        $this->assertArrayHasKey('input', $result);
        $this->assertArrayHasKey('service', $result);
        $this->assertSame('test', $result['input']);
        $this->assertInstanceOf(FakeService::class, $result['service']);
        $this->assertSame('CustomService', $result['service']->name);
    }

    public function testNamedAttributeSuccess(): void
    {
        // Test successful Named attribute resolution with string
        $injector = new Injector(new class extends AbstractModule {
            protected function configure(): void
            {
                $this->bind()->annotatedWith('debug')->toInstance('DEBUG_LEVEL');
            }
        });

        $args = new BecomingArguments($injector);
        $result = ($args)(new FakeInputData('test'), FakeWithNamed::class);

        $this->assertArrayHasKey('input', $result);
        $this->assertArrayHasKey('logLevel', $result);
        $this->assertSame('test', $result['input']);
        $this->assertSame('DEBUG_LEVEL', $result['logLevel']);
    }

    public function testNamedAttributeThrowsException(): void
    {
        // Test that Named attribute for scalar requires binding
        $this->expectException(Throwable::class);

        $injector = new Injector();
        $args = new BecomingArguments($injector);

        // This should fail because no binding for #[Named('debug')] string
        ($args)(new FakeInputData('test'), FakeWithNamed::class);
    }
}
