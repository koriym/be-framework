# 8. Error Handling & Validation

> "What cannot be must be understood. Failure preserves meaning through clear language."

Error handling in Be Framework is not about catching exceptions—it's about **preserving meaning when existence fails**.

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
    foreach ($e->getErrors()->exceptions as $exception) {
        echo get_class($exception) . ": " . $exception->getMessage();
        // EmptyNameException: Name cannot be empty.
        // InvalidEmailFormatException: Email format is invalid.
        // AgeTooYoungException: Age must be at least 13.
    }
}
```

## Exception Hierarchy

Domain exceptions form meaningful categories:

```php
abstract class DomainException extends Exception {}

final class EmptyNameException extends DomainException {}

final class InvalidEmailFormatException extends DomainException
{
    public function __construct(public readonly string $invalidEmail)
    {
        parent::__construct("Email format is invalid: {$invalidEmail}");
    }
}

// Age-related existence failures
abstract class AgeException extends DomainException {}
final class NegativeAgeException extends AgeException {}
final class AgeTooHighException extends AgeException {}
```

## Multilingual Error Messages

Semantic exceptions speak the user's language:

```php
#[Message([
    'en' => 'Name cannot be empty.',
    'ja' => '名前は空にできません。',
    'es' => 'El nombre no puede estar vacío.'
])]
final class EmptyNameException extends DomainException {}

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

## Error Recovery Patterns

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
        try {
            $this->being = new ValidUser($name, $email, $age);
        } catch (ValidationException $e) {
            $this->being = new InvalidUser($e->getErrors());
        }
    }
}
```

## Semantic Logging Integration

Validation failures are automatically logged with context:

```php
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
        }
    ]
}
```

## Development vs Production

```php
// Development: Verbose error details
if (app()->environment('local')) {
    $errors->getDetailedMessages();
}

// Production: User-friendly messages
$errors->getMessages('en');
// ["Name cannot be empty.", "Email format is invalid."]
```

## Testing Error Conditions

```php
public function testCollectsAllValidationErrors(): void
{
    try {
        $becoming(new UserInput('', 'invalid-email', -5));
        $this->fail('Expected SemanticVariableException');
    } catch (SemanticVariableException $e) {
        $errors = $e->getErrors();
        $this->assertCount(3, $errors->exceptions);
    }
}
```

## The Revolution

Semantic exceptions transform error handling from **problem reporting** to **meaning preservation**.

When existence fails, the reason becomes **clear, actionable, and multilingual**.

Errors are not obstacles—they are **valid beings** that guide users toward successful transformation.

---

**Next**: Learn about [The Philosophy Behind](09-philosophy-behind.md) to understand the deeper principles.

*"Semantic exceptions don't just report failure—they preserve the meaning of what cannot exist."*