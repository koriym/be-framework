# 5. Semantic Variables

> "What should exist must be valid. What cannot exist will never be born."

Semantic Variables embody Be Framework's deepest principle: **only meaningful beings can exist**. They transform the type system from mechanical checking into ontological truth.

## Beyond Generic Types

Traditional types tell us little about meaning:

```php
function createUser(string $name, string $email, int $age) {
    // We must defend against the meaningless
    if (empty($name)) throw new Exception();
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new Exception();
    // ... endless defensive programming
}
```

## Types That Carry Truth

Semantic Variables change the fundamental question from "Is this valid?" to "Can this exist?"

```php
function createUser(PersonName $name, EmailAddress $email, Age $age) {
    // If we reach here, existence is already guaranteed
    // The types themselves are the validation
}
```

## The Structure of Meaning

Every semantic variable defines what can exist in its domain:

```php
final class Name
{
    #[Validate]
    public function validate(string $name): void
    {
        if (empty(trim($name))) {
            throw new EmptyNameException();
        }
    }

    #[Validate]
    public function validateEnglish(#[English] string $name): void
    {
        if (!preg_match('/^[A-Za-z\s]+$/', $name)) {
            throw new InvalidNameFormatException($name);
        }
    }
}
```

## Exceptions as Ontological Boundaries

When existence fails, meaning must be preserved through language:

```php
#[Message([
    'en' => 'Name cannot be empty.',
    'ja' => '名前は空にできません。'
])]
final class EmptyNameException extends DomainException
{
}
```

The `#[Message]` attribute bridges human understanding with computational reality—when existence fails, the reason must be comprehensible.

## Semantic Variables in Action

### In Being Constructors

Semantic variables are automatically validated when used in constructors:

```php
final readonly class UserProfile
{
    public readonly string $displayName;
    public readonly string $email;

    public function __construct(
        #[Input] #[English] public string $name,    // Auto-validated as English name
        #[Input] string $emailAddress,              // Auto-validated as email
        #[Inject] NameFormatter $formatter
    ) {
        // At this point, all inputs are guaranteed valid
        $this->displayName = $formatter->format($name);
        $this->email = $emailAddress;
    }
}
```

### Context-Aware Validation

Semantic variables can have **multiple validation methods** for different contexts:

```php
final class ProductCode
{
    #[Validate]
    public function validate(string $code): void
    {
        // Basic format validation
        if (!preg_match('/^[A-Z]{2,4}-\d{3,6}$/', $code)) {
            throw new InvalidProductCodeFormatException($code);
        }
    }

    #[Validate] 
    public function validateLegacy(#[Legacy] string $code): void
    {
        // Legacy system compatibility
        if (!preg_match('/^[0-9]{6}$/', $code)) {
            throw new InvalidLegacyCodeException($code);
        }
    }

    #[Validate]
    public function validatePremium(#[Premium] string $code): void
    {
        // Premium product validation
        if (!str_starts_with($code, 'PREM-')) {
            throw new NotPremiumProductException($code);
        }
    }
}
```

## Advanced Semantic Variable Patterns

### Hierarchical Validation

Semantic variables can build upon each other:

```php
final class Age
{
    #[Validate]
    public function validate(int $age): void
    {
        if ($age < 0) throw new NegativeAgeException();
        if ($age > 150) throw new AgeTooHighException();
    }
}

final class TeenAge  
{
    #[Validate]
    public function validate(#[Teen] int $age): void
    {
        // Inherits basic Age validation, adds teen-specific rules
        if ($age < 13) throw new TeenAgeTooYoungException();
        if ($age > 19) throw new TeenAgeTooOldException();
    }
}
```

### Composite Semantic Variables

Complex business rules can be expressed through composition:

```php
final class PaymentAmount
{
    #[Validate]
    public function validate(float $amount): void
    {
        if ($amount <= 0) {
            throw new InvalidPaymentAmountException();
        }
    }

    #[Validate]
    public function validateSubscription(#[Subscription] float $amount): void
    {
        // Subscription-specific rules
        if ($amount < 5.00) {
            throw new SubscriptionTooSmallException();
        }
        if ($amount > 999.99) {
            throw new SubscriptionTooLargeException();
        }
    }
}
```

## Semantic Tags for Context

Tags provide **context information** that influences validation:

```php
namespace Be\App\Tag;

/**
 * Marks content as English language
 */
final class English
{
}

/**
 * Marks content as suitable for teenagers  
 */
final class Teen
{
}

/**
 * Marks product as premium tier
 */
final class Premium
{
}
```

Tags are used in Being constructors to specify validation context:

```php
final readonly class InternationalGreeting
{
    public function __construct(
        #[Input] #[English] public string $name,     // English name validation
        #[Input] #[Japanese] public string $title,   // Japanese title validation
        #[Inject] TranslationService $translator
    ) {
        // Both inputs validated according to their linguistic context
    }
}
```

## Error Handling and User Experience

### Automatic Error Collection

The framework automatically collects **all validation errors** during metamorphosis:

```php
try {
    $userProfile = $becoming(new UserRegistrationInput($data));
} catch (SemanticVariableException $e) {
    // Get all validation errors at once
    $errors = $e->getErrors();
    
    // Multilingual error messages
    $englishMessages = $errors->getMessages('en');
    $japaneseMessages = $errors->getMessages('ja');
    
    // Specific error details
    foreach ($errors->exceptions as $exception) {
        echo get_class($exception) . ": " . $exception->getMessage() . "\n";
    }
}
```

### Localized Error Messages

Error messages automatically adapt to user language:

```php
// For English users
$errors->getMessages('en');
// ['Name cannot be empty.', 'Invalid email format.']

// For Japanese users  
$errors->getMessages('ja');
// ['名前は空にできません。', 'メールの形式が無効です。']
```

## Benefits of Semantic Variables

### 1. **Impossible States Cannot Exist**
Once an object is constructed, all its properties are **guaranteed valid**. No defensive programming needed.

### 2. **Self-Documenting Code**
The semantic variable name tells you exactly what business rules apply:
```php
function processOrder(ProductCode $product, PaymentAmount $amount, CustomerAge $age)
{
    // The signature IS the documentation
}
```

### 3. **Centralized Validation Logic**
All validation rules for a domain concept live in one place, not scattered across controllers.

### 4. **Context-Aware Validation**
Same data type, different validation rules based on usage context.

### 5. **Multilingual Error Messages**
Automatic internationalization of error messages through attributes.

### 6. **Composable Domain Rules**
Complex validation can be built by composing simpler semantic variables.

## Best Practices

### Naming Conventions
- Use domain language: `CustomerAge` not `IntegerAge`
- Be specific: `EmailAddress` not `Email`
- Include context: `ProductCode` not `Code`

### Validation Methods
- Keep validation methods **pure** (no side effects)
- Use **specific exceptions** for each error type
- Include **context information** in exception messages

### Error Messages
- Write **user-friendly messages**, not technical ones
- Include **specific information** when helpful
- Support **multiple languages** from the start

---

**Next**: Learn about [Type-Driven Metamorphosis](06-type-driven-metamorphosis.md) where objects discover their own nature through union types.

*"Semantic Variables transform the type system from mechanical constraint checking into meaningful business rule expression. They don't just validate data—they ensure only meaningful beings can exist."*