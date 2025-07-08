<?php

declare(strict_types=1);

use Ray\Di\AbstractModule;
use Ray\Framework\Ray;
use Ray\Framework\Be;
use Ray\Di\Injector;
use Ray\InputQuery\Attribute\Input;
use Ray\Di\Di\Inject;

require_once 'vendor/autoload.php';

// =============================================================================
// DEMO OBJECTS - The Metamorphosis Chain
// =============================================================================

/**
 * User input to be validated
 */
#[Be(ValidationAttempt::class)]
final class UserInput
{
    public function __construct(
        #[Input] public readonly string $name
    ) {}
}

/**
 * Validation attempt - determines success or failure
 */
#[Be([ValidUser::class, ErrorResponse::class])]
final class ValidationAttempt
{
    public readonly Success|Failure $being;

    public function __construct(
        #[Input] string $name,
        #[Inject] DataValidatorInterface $validator
    ) {
        $this->being = $validator->isValid($name)
            ? new Success($name, 'Valid user name')
            : new Failure($validator->getErrors($name));
    }
}

/**
 * Valid user after successful validation
 */
final class ValidUser
{
    public function __construct(
        #[Input] public readonly Success $being
    ) {}
}

/**
 * Error response containing user input and error details
 */
final class ErrorResponse
{
    public function __construct(
        #[Input] public readonly Failure $being
    ) {}
}

// =============================================================================
// VALUE OBJECTS - The essence of being
// =============================================================================

final class Success
{
    public function __construct(
        #[Input] public readonly string $name,
        #[Input] public readonly string $message
    ) {}
}

final class Failure
{
    public function __construct(
        #[Input] public readonly string $error
    ) {}
}

// =============================================================================
// SERVICES - Injected capabilities
// =============================================================================

interface DataValidatorInterface
{
    public function isValid(string $data): bool;
    public function getErrors(string $data): string;
}

final class SimpleValidator implements DataValidatorInterface
{
    public function isValid(string $data): bool
    {
        return !empty($data) && strlen($data) > 3;
    }

    public function getErrors(string $data): string
    {
        if (empty($data)) {
            return 'Data cannot be empty';
        }
        if (strlen($data) <= 3) {
            return 'Data must be longer than 3 characters';
        }
        return '';
    }
}

// =============================================================================
// DEMO EXECUTION
// =============================================================================

echo "Ray.Framework - Metamorphic Programming Demo\n\n";

// Setup DI container
$injector = new Injector(new class extends AbstractModule{
    protected function configure(): void
    {
        // Bind the DataValidator interface to its implementation
        $this->bind(DataValidatorInterface::class)->to(SimpleValidator::class);
    }
});

// Create Ray framework instance
$ray = new Ray($injector);

$userNames = ['Alice', 'Bo', ''];

foreach ($userNames as $name) {
    $userInput = new UserInput(name: $name);
    $maybeUser = $ray($userInput);

    if ($maybeUser instanceof ValidUser) {
        echo "'{$name}' -> ValidUser: {$maybeUser->being->name}\n";
    } elseif ($maybeUser instanceof ErrorResponse) {
        echo "'{$name}' -> ErrorResponse: {$maybeUser->being->error}\n";
    }
}

