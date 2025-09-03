<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Be\Framework\Attribute\Be;
use Be\Framework\Becoming;
use Ray\Di\AbstractModule;
use Ray\Di\Di\Inject;
use Ray\Di\Injector;
use Ray\InputQuery\Attribute\Input;

// Sample application demonstrating Be Framework's metamorphosis

// Step 1: User Input - Initial state
#[Be(BeingUser::class)]
final class UserInput
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
        public readonly int $age,
    ) {}
}

// Step 2: User Being - Validation and transformation
#[Be([ValidUser::class, InvalidUser::class])]
final class BeingUser
{
    public function __construct(
        #[Input] public readonly string $email,
        #[Input] public readonly string $password,
        #[Input] public readonly int $age,
        #[Inject] public readonly EmailValidator $validator,
    ) {}
}

// Step 3: Valid User - Success path with registration
#[Be(RegisteredUser::class)]
final class ValidUser
{
    public function __construct(
        #[Input] string $email,
        #[Input] string $password,
        #[Input] int $age,
        #[Input] EmailValidator $validator,
    ) {
        if (!$validator->isValid($email)) {
            throw new InvalidArgumentException('Invalid email format');
        }
        
        if ($age < 18) {
            throw new InvalidArgumentException('User must be 18 or older');
        }
        
        if (strlen($password) < 8) {
            throw new InvalidArgumentException('Password must be at least 8 characters');
        }
        
        $this->email = $email;
        $this->hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $this->age = $age;
        $this->status = 'valid';
    }

    public readonly string $email;
    public readonly string $hashedPassword;
    public readonly int $age;
    public readonly string $status;
}

// Step 3 Alternative: Invalid User - Failure path
final class InvalidUser
{
    public function __construct(
        #[Input] string $email,
        #[Input] string $password,
        #[Input] int $age,
        #[Input] EmailValidator $validator,
    ) {
        // Accepts any input that doesn't match valid criteria
        $this->email = $email;
        $this->errors = [];
        
        if (!$validator->isValid($email)) {
            $this->errors[] = 'Invalid email format';
        }
        
        if ($age < 18) {
            $this->errors[] = 'User must be 18 or older';
        }
        
        if (strlen($password) < 8) {
            $this->errors[] = 'Password must be at least 8 characters';
        }
        
        $this->status = 'invalid';
    }

    public readonly string $email;
    public readonly array $errors;
    public readonly string $status;
}

// Step 4: Registered User - Final transformation with welcome message
final class RegisteredUser
{
    public function __construct(
        #[Input] string $email,
        #[Input] string $hashedPassword,
        #[Input] int $age,
        #[Input] string $status,
        #[Inject] UserRepository $repository,
        #[Inject] NotificationService $notificationService,
    ) {
        $this->userId = $repository->save($email, $hashedPassword, $age);
        $this->email = $email;
        $this->age = $age;
        $this->registeredAt = new DateTimeImmutable();
        $this->welcomeMessage = $notificationService->sendWelcome($email);
        $this->status = 'registered';
    }

    public readonly int $userId;
    public readonly string $email;
    public readonly int $age;
    public readonly DateTimeImmutable $registeredAt;
    public readonly string $welcomeMessage;
    public readonly string $status;
}

// Dependencies - Services injected into the metamorphosis

final class EmailValidator
{
    public function isValid(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

final class UserRepository
{
    private static int $nextId = 1;
    
    public function save(string $email, string $hashedPassword, int $age): int
    {
        // Simulate saving to database
        $userId = self::$nextId++;
        echo "💾 Saving user to database: ID={$userId}, Email={$email}, Age={$age}\n";
        return $userId;
    }
}

final class NotificationService
{
    public function sendWelcome(string $email): string
    {
        $message = "Welcome to our platform, {$email}! 🎉";
        echo "📧 Sending welcome email: {$message}\n";
        return $message;
    }
}

// Dependency injection configuration
final class SampleAppModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->bind(EmailValidator::class);
        $this->bind(UserRepository::class);
        $this->bind(NotificationService::class);
    }
}

// Application execution
function runSampleApp(): void
{
    echo "🚀 Be Framework Sample Application - User Registration\n";
    echo "=" . str_repeat("=", 50) . "\n\n";
    
    // Setup dependency injection
    $injector = new Injector(new SampleAppModule());
    $becoming = new Becoming($injector);
    
    // Test Case 1: Valid user registration
    echo "📝 Test Case 1: Valid User Registration\n";
    echo "-" . str_repeat("-", 40) . "\n";
    
    $validInput = new UserInput(
        email: 'john@example.com',
        password: 'SecurePassword123',
        age: 25
    );
    
    echo "Input: Email={$validInput->email}, Age={$validInput->age}\n";
    
    try {
        $result = $becoming($validInput);
        
        if ($result instanceof RegisteredUser) {
            echo "✅ Success! User registered:\n";
            echo "   - User ID: {$result->userId}\n";
            echo "   - Email: {$result->email}\n";
            echo "   - Age: {$result->age}\n";
            echo "   - Status: {$result->status}\n";
            echo "   - Registered: {$result->registeredAt->format('Y-m-d H:i:s')}\n";
            echo "   - Welcome: {$result->welcomeMessage}\n";
        }
    } catch (Exception $e) {
        echo "❌ Error: {$e->getMessage()}\n";
    }
    
    echo "\n" . str_repeat("=", 60) . "\n\n";
    
    // Test Case 2: Invalid user (too young)
    echo "📝 Test Case 2: Invalid User (Age < 18)\n";
    echo "-" . str_repeat("-", 40) . "\n";
    
    $invalidInput = new UserInput(
        email: 'teen@example.com',
        password: 'ValidPassword123',
        age: 16
    );
    
    echo "Input: Email={$invalidInput->email}, Age={$invalidInput->age}\n";
    
    try {
        $result = $becoming($invalidInput);
        
        if ($result instanceof InvalidUser) {
            echo "❌ User validation failed:\n";
            echo "   - Email: {$result->email}\n";
            echo "   - Status: {$result->status}\n";
            echo "   - Errors: " . implode(', ', $result->errors) . "\n";
        }
    } catch (Exception $e) {
        echo "❌ Error: {$e->getMessage()}\n";
    }
    
    echo "\n" . str_repeat("=", 60) . "\n\n";
    
    // Test Case 3: Invalid email and password
    echo "📝 Test Case 3: Multiple Validation Errors\n";
    echo "-" . str_repeat("-", 40) . "\n";
    
    $multiErrorInput = new UserInput(
        email: 'not-an-email',
        password: 'weak',
        age: 25
    );
    
    echo "Input: Email={$multiErrorInput->email}, Password='{$multiErrorInput->password}', Age={$multiErrorInput->age}\n";
    
    try {
        $result = $becoming($multiErrorInput);
        
        if ($result instanceof InvalidUser) {
            echo "❌ User validation failed:\n";
            echo "   - Email: {$result->email}\n";
            echo "   - Status: {$result->status}\n";
            echo "   - Errors:\n";
            foreach ($result->errors as $error) {
                echo "     • {$error}\n";
            }
        }
    } catch (Exception $e) {
        echo "❌ Error: {$e->getMessage()}\n";
    }
    
    echo "\n🏁 Sample application completed!\n";
}

// Run the application
runSampleApp();