<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use Be\Framework\Attribute\Be;
use Be\Framework\Becoming;
use Ray\Di\Injector;
use Ray\InputQuery\Attribute\Input;

// Input with validation choice
#[Be([ValidUser::class, InvalidUser::class])]
final class UserInput
{
    public function __construct(
        public readonly string $email,
        public readonly int $age
    ) {}
}

// Valid user path
final class ValidUser
{
    public function __construct(
        #[Input] string $email,  // Immanent - from UserInput
        #[Input] int $age        // Immanent - from UserInput
    ) {
        // Validation logic in constructor
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email format');
        }
        if ($age < 18) {
            throw new InvalidArgumentException('User must be 18 or older');
        }
        
        $this->email = $email;
        $this->age = $age;
        $this->status = 'valid';
    }
    
    public readonly string $email;
    public readonly int $age;
    public readonly string $status;
}

// Invalid user path - accepts all input without validation exceptions
final class InvalidUser
{
    public function __construct(
        #[Input] public readonly string $email,  // Immanent - from UserInput
        #[Input] public readonly int $age        // Immanent - from UserInput
    ) {
        // Calculate errors but don't throw exceptions
        $errors = [];
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        }
        if ($age < 18) {
            $errors[] = 'User must be 18 or older';
        }
        
        $this->status = 'invalid';
        $this->errors = $errors;
    }
    
    public readonly string $status;
    public readonly array $errors;
}

$becoming = new Becoming(new Injector());

echo "=== Valid User Example ===\n";
$validInput = new UserInput('user@example.com', 25);
$result1 = $becoming($validInput);
echo "Result class: " . $result1::class . "\n";
echo "Status: " . $result1->status . "\n";
echo "Email: " . $result1->email . "\n";
echo "Age: " . $result1->age . "\n\n";

echo "=== Invalid User Example ===\n";
$invalidInput = new UserInput('invalid-email', 16);
$result2 = $becoming($invalidInput);
echo "Result class: " . $result2::class . "\n";
echo "Status: " . $result2->status . "\n";
echo "Email: " . $result2->email . "\n";
echo "Age: " . $result2->age . "\n";
echo "Errors: " . implode(', ', $result2->errors) . "\n";