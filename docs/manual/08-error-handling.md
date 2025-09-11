# 8. Error Handling & Validation

> "What cannot be must be understood. Failure preserves meaning through clear language."

Error handling in Be Framework is not about catching exceptions—it's about **preserving meaning when existence fails**. Semantic exceptions bridge the gap between computational failure and human understanding.

## Beyond Generic Exceptions

Traditional error handling loses meaning:

```php
try {
    $user = new User($name, $email, $age);
} catch (Exception $e) {
    // What went wrong? Why? How to fix it?
    echo $e->getMessage();  // "Validation failed"
}
```

## Semantic Exceptions: Meaning in Failure

Every failure carries **specific ontological meaning**:

```php
try {
    $user = $becoming(new UserInput($name, $email, $age));
} catch (SemanticVariableException $e) {
    // Complete understanding of what cannot exist and why
    foreach ($e->getErrors()->exceptions as $exception) {
        echo get_class($exception) . ": " . $exception->getMessage();
        // EmptyNameException: Name cannot be empty.
        // InvalidEmailFormatException: Email format is invalid.
        // AgeTooYoungException: Age must be at least 13.
    }
}
```

## Exception Hierarchy: Ontological Categories

### Domain Exceptions

```php
namespace App\Exception;

abstract class DomainException extends Exception
{
    // Base for all domain-specific failures
}

final class EmptyNameException extends DomainException
{
}

final class InvalidEmailFormatException extends DomainException
{
    public function __construct(public readonly string $invalidEmail)
    {
        parent::__construct("Email format is invalid: {$invalidEmail}");
    }
}
```

### Validation Categories

```php
// Age-related existence failures
abstract class AgeException extends DomainException {}

final class NegativeAgeException extends AgeException {}
final class AgeTooHighException extends AgeException {}
final class AgeTooYoungException extends AgeException {}

// Email-related existence failures  
abstract class EmailException extends DomainException {}

final class InvalidEmailFormatException extends EmailException {}
final class EmailAlreadyExistsException extends EmailException {}
final class DisposableEmailException extends EmailException {}
```

## Multilingual Error Messages

Semantic exceptions speak the user's language:

```php
#[Message([
    'en' => 'Name cannot be empty.',
    'ja' => '名前は空にできません。',
    'es' => 'El nombre no puede estar vacío.',
    'fr' => 'Le nom ne peut pas être vide.'
])]
final class EmptyNameException extends DomainException
{
}

#[Message([
    'en' => 'Age must be between {min} and {max} years.',
    'ja' => '年齢は{min}歳から{max}歳の間でなければなりません。'
])]
final class AgeOutOfRangeException extends DomainException
{
    public function __construct(
        public readonly int $age,
        public readonly int $min = 0,
        public readonly int $max = 150
    ) {}
}
```

## Automatic Error Collection

The framework collects **all validation failures** before throwing:

```php
final class UserValidation
{
    public function __construct(
        #[Input] string $name,      // May throw EmptyNameException
        #[Input] string $email,     // May throw InvalidEmailFormatException  
        #[Input] int $age           // May throw NegativeAgeException
    ) {
        // If ANY validation fails, ALL errors are collected
        // Single SemanticVariableException contains everything
    }
}
```

No "fail fast"—**fail completely with full understanding**.

## Error Context and Details

### Rich Error Information

```php
final class PaymentValidationException extends DomainException
{
    public function __construct(
        public readonly Money $attemptedAmount,
        public readonly Money $availableBalance,
        public readonly PaymentMethod $method,
        public readonly DateTime $attemptedAt
    ) {
        $message = "Payment of {$attemptedAmount} failed. " .
                  "Available balance: {$availableBalance}. " .
                  "Method: {$method->type}.";
        parent::__construct($message);
    }
}
```

### Contextual Error Details

```php
#[Message([
    'en' => 'Product code "{code}" is not valid for {context} products.',
    'ja' => '商品コード「{code}」は{context}商品には無効です。'
])]
final class InvalidProductCodeException extends DomainException
{
    public function __construct(
        public readonly string $code,
        public readonly string $context = 'standard'
    ) {}
}
```

## Error Recovery Patterns

### Validation as Transformation

Errors become **valid beings** in their own right:

```php
#[Be([ValidUser::class, InvalidUser::class])]
final class UserValidation
{
    public readonly ValidUser|InvalidUser $being;
    
    public function __construct(
        #[Input] string $name,
        #[Input] string $email,
        #[Input] int $age
    ) {
        $errors = [];
        
        try {
            // Validate each component
            $validatedName = $this->validateName($name);
            $validatedEmail = $this->validateEmail($email);
            $validatedAge = $this->validateAge($age);
            
            $this->being = new ValidUser($validatedName, $validatedEmail, $validatedAge);
        } catch (ValidationException $e) {
            $this->being = new InvalidUser($e->getErrors());
        }
    }
}
```

### Error Accumulation

```php
final class FormValidation
{
    public readonly array $errors;
    public readonly bool $isValid;
    
    public function __construct(
        #[Input] array $formData,
        #[Inject] ValidationService $validator
    ) {
        $this->errors = [];
        
        // Collect all errors, don't stop at first failure
        foreach ($formData as $field => $value) {
            try {
                $validator->validateField($field, $value);
            } catch (FieldValidationException $e) {
                $this->errors[$field] = $e;
            }
        }
        
        $this->isValid = empty($this->errors);
    }
}
```

## Debugging and Observability

### Semantic Logging Integration

Validation failures are automatically logged with context:

```php
// Semantic log entry for validation failure
{
    "event": "metamorphosis_failed",
    "source_class": "UserInput",
    "destination_class": "UserProfile", 
    "errors": [
        {
            "exception": "EmptyNameException",
            "message": "Name cannot be empty",
            "field": "name",
            "value": ""
        },
        {
            "exception": "InvalidEmailFormatException",
            "message": "Email format is invalid",
            "field": "email", 
            "value": "not-an-email"
        }
    ],
    "timestamp": "2024-03-15T10:30:00Z"
}
```

### Development vs Production

```php
// Development: Verbose error details
if (app()->environment('local')) {
    $errors->getDetailedMessages();
    // [
    //     "EmptyNameException: Name cannot be empty. Field: 'name', Value: '', Location: UserInput:15"
    // ]
}

// Production: User-friendly messages
$errors->getMessages('en');
// ["Name cannot be empty.", "Email format is invalid."]
```

## Testing Error Conditions

### Exception Testing

```php
public function testEmptyNameThrowsException(): void
{
    $this->expectException(SemanticVariableException::class);
    
    $becoming = new Becoming();
    $becoming(new UserInput('', 'test@example.com', 25));
}

public function testCollectsAllValidationErrors(): void
{
    try {
        $becoming = new Becoming();
        $becoming(new UserInput('', 'invalid-email', -5));
        
        $this->fail('Expected SemanticVariableException');
    } catch (SemanticVariableException $e) {
        $errors = $e->getErrors();
        
        $this->assertCount(3, $errors->exceptions);
        $this->assertInstanceOf(EmptyNameException::class, $errors->exceptions[0]);
        $this->assertInstanceOf(InvalidEmailFormatException::class, $errors->exceptions[1]);
        $this->assertInstanceOf(NegativeAgeException::class, $errors->exceptions[2]);
    }
}
```

### Error Message Testing

```php
public function testMultilingualErrorMessages(): void
{
    try {
        // Trigger validation failure
        $becoming(new UserInput('', 'test@example.com', 25));
    } catch (SemanticVariableException $e) {
        $errors = $e->getErrors();
        
        $englishMessages = $errors->getMessages('en');
        $japaneseMessages = $errors->getMessages('ja');
        
        $this->assertContains('Name cannot be empty.', $englishMessages);
        $this->assertContains('名前は空にできません。', $japaneseMessages);
    }
}
```

## Best Practices

### Exception Design
- Create **specific exception classes** for each failure type
- Include **relevant context** in exception properties
- Use **meaningful inheritance hierarchies**

### Error Messages
- Write for **end users**, not developers
- Include **actionable information** when possible
- Support **internationalization** from the start

### Validation Strategy
- **Collect all errors** before throwing
- Preserve **complete context** of what failed
- Design exceptions as **first-class domain objects**

### Error Recovery
- Treat errors as **valid beings** when appropriate
- Design for **graceful degradation**
- Provide **clear paths forward** for users

---

**Next**: Learn about [The Philosophy Behind](09-philosophy-behind.md) to understand the deeper principles.

*"Semantic exceptions don't just report failure—they preserve the meaning of what cannot exist, creating understanding from impossibility."*