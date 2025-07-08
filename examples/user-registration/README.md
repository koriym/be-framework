# User Registration Example

This example demonstrates the Metamorphosis Architecture in action through a complete user registration flow.

## Overview

The registration process showcases how Ray.Framework handles:
- Input validation
- Business logic branching
- Success and failure paths
- Response generation

All through pure constructor-driven transformations.

## The Metamorphosis Flow

```
RegistrationInput
    │
    ├─ Constructor receives raw input data
    └─ No validation yet (The Egg)
    ↓
ValidatedRegistration
    │
    ├─ Validates email format
    ├─ Validates password strength
    ├─ Checks if email already exists
    └─ Discovers its own destiny through $being property (The Larva)
    ↓
Type-Driven Branching (No Router Needed!)
    │
    ├─ If $being instanceof UserInput ──→ UnverifiedUser
    │                                          ↓
    │                                    VerificationEmailSent
    │                                          ↓
    │                                    JsonResponse (201 Created)
    │
    └─ If $being instanceof ConflictingUser ──→ UserConflict
                                                      ↓
                                                JsonResponse (409 Conflict)
```

## Key Patterns Demonstrated

### 1. Traffic Controller Pattern

The `RegistrationRouter` shows how to handle branching without violating the constructor-only principle:

```php
public function __construct(
    #[Input] ValidatedRegistration $validated,
    UserRepository $userRepo,
    UnverifiedUserFactory $unverifiedUserFactory,
    UserConflictFactory $userConflictFactory
) {
    if ($userRepo->existsByEmail($validated->email)) {
        $userConflictFactory->create($validated->email);
        return;
    }
    
    $unverifiedUserFactory->create($validated->email, $validated->password);
}
```

### 2. Type-Safe Factories

Instead of generic factories, we use dedicated interfaces:
- `UnverifiedUserFactory` - Creates users in the success path
- `UserConflictFactory` - Handles duplicate email conflicts

This ensures compile-time type safety and clear intent.

### 3. Constructor as Validator

The `ValidatedRegistration` class demonstrates validation through existence:

```php
public function __construct(
    #[Input] public readonly string $email,
    #[Input] public readonly string $password,
    #[Input] string $passwordConfirmation,
    UserValidator $validator
) {
    // If validation fails, this object never exists
    $validator->validateEmailFormat($this->email);
    $validator->validatePasswordStrength($this->password);
    $validator->validatePasswordsMatch($this->password, $passwordConfirmation);
}
```

### 4. Immutable State

All properties are `public readonly`, ensuring:
- No state mutations after creation
- Clear data flow
- Predictable behavior

## Running the Example

```php
use Ray\Framework\Ray;
use Ray\Di\Injector;

$injector = new Injector(new RegistrationModule());
$ray = new Ray($injector);

$response = $ray(new RegistrationInput(
    'newuser@example.com',
    'SecurePass123!',
    'SecurePass123!'
));

header('HTTP/1.1 ' . $response->statusCode);
foreach ($response->headers as $name => $value) {
    header("{$name}: {$value}");
}
echo $response->json;
```

## Expected Responses

### Success (201 Created)
```json
{
    "message": "Registration successful. Please check your email to verify your account.",
    "userId": "12345"
}
```

### Conflict (409 Conflict)
```json
{
    "error": "User already exists",
    "message": "The email address 'existing@example.com' is already registered."
}
```

## Testing Strategy

### Testing Regular Metamorphosis Classes

For standard metamorphosis classes, test the constructor logic directly:

```php
class ValidatedRegistrationTest extends TestCase
{
    public function testValidRegistration(): void
    {
        $validator = $this->createMock(UserValidator::class);
        $validator->expects($this->once())
            ->method('validateEmailFormat')
            ->with('user@example.com');
            
        $validated = new ValidatedRegistration(
            'user@example.com',
            'SecurePass123!',
            'SecurePass123!',
            $validator
        );
        
        $this->assertEquals('user@example.com', $validated->email);
        $this->assertEquals('SecurePass123!', $validated->password);
    }
    
    public function testInvalidEmailThrowsException(): void
    {
        $validator = $this->createMock(UserValidator::class);
        $validator->method('validateEmailFormat')
            ->willThrowException(new ValidationException('Invalid email'));
            
        $this->expectException(ValidationException::class);
        
        new ValidatedRegistration(
            'invalid-email',
            'SecurePass123!',
            'SecurePass123!',
            $validator
        );
    }
}
```

### Testing Traffic Controllers

To test the Traffic Controller pattern, use the Spy pattern:

```php
class RegistrationRouterTest extends TestCase
{
    public function testRouteToSuccessPath(): void
    {
        $userRepo = $this->createMock(UserRepository::class);
        $userRepo->method('existsByEmail')->willReturn(false);
        
        $spySuccess = new SpyUnverifiedUserFactory();
        $spyConflict = new SpyUserConflictFactory();
        
        new RegistrationRouter(
            new ValidatedRegistration(/* ... */),
            $userRepo,
            $spySuccess,
            $spyConflict
        );
        
        // Assert success path was taken
        $this->assertEquals(1, $spySuccess->callCount);
        $this->assertEquals(0, $spyConflict->callCount);
    }
    
    public function testRouteToConflictPath(): void
    {
        $userRepo = $this->createMock(UserRepository::class);
        $userRepo->method('existsByEmail')->willReturn(true);
        
        $spySuccess = new SpyUnverifiedUserFactory();
        $spyConflict = new SpyUserConflictFactory();
        
        new RegistrationRouter(
            new ValidatedRegistration(/* ... */),
            $userRepo,
            $spySuccess,
            $spyConflict
        );
        
        // Assert conflict path was taken
        $this->assertEquals(0, $spySuccess->callCount);
        $this->assertEquals(1, $spyConflict->callCount);
    }
}

class SpyUnverifiedUserFactory implements UnverifiedUserFactory
{
    public int $callCount = 0;
    public array $capturedArgs = [];
    
    public function create(string $email, string $password): UnverifiedUser
    {
        $this->callCount++;
        $this->capturedArgs = [$email, $password];
        return new UnverifiedUser(/* mocked */);
    }
}
```

## Learn More

- See the [Metamorphosis Architecture Manifesto](../../docs/metamorphosis-architecture-manifesto.md) for detailed patterns
- Read the [Ray.Framework Whitepaper](../../docs/ray-framework-whitepaper.md) for philosophical foundations
- Explore [Ontological Programming](../../docs/ontological-programming-paper.md) for the underlying paradigm
