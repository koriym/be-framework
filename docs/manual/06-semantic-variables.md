# 5. Semantic Variables

> "What should exist must be valid. What cannot exist will never be born."

Semantic Variables embody Be Framework's deepest principle: **only meaningful beings can exist**.

## The Problem

Traditional types defend against the meaningless:

```php
function createUser(string $name, string $email, int $age) {
    if (empty($name)) throw new Exception();
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new Exception();
    // ... endless defensive programming
}
```

## The Solution

Semantic Variables change the fundamental question from "Is this valid?" to "Can this exist?"

```php
function createUser(PersonName $name, EmailAddress $email, Age $age) {
    // If we reach here, existence is already guaranteed
}
```

## Defining Existence

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
}
```

Multiple validation contexts exist naturally:

```php
final class ProductCode
{
    #[Validate]
    public function validate(string $code): void { /* standard rules */ }

    #[Validate] 
    public function validateLegacy(#[Legacy] string $code): void { /* legacy rules */ }

    #[Validate]
    public function validatePremium(#[Premium] string $code): void { /* premium rules */ }
}
```

## Meaningful Failure

When existence fails, meaning must be preserved:

```php
#[Message([
    'en' => 'Name cannot be empty.',
    'ja' => '名前は空にできません。'
])]
final class EmptyNameException extends DomainException {}
```

The framework collects **all validation errors** before throwing, creating complete understanding of what cannot exist.

## Natural Integration

Semantic variables work automatically in Being constructors:

```php
final readonly class UserProfile
{
    public function __construct(
        #[Input] #[English] public string $name,    // Auto-validated as English name
        #[Input] string $emailAddress,              // Auto-validated as email
        #[Inject] NameFormatter $formatter
    ) {
        // At this point, all inputs are guaranteed valid
    }
}
```

## Hierarchical Validation

Semantic variables can build upon each other:

```php
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

## Error Handling

Multilingual error messages adapt automatically:

```php
try {
    $userProfile = $becoming(new UserRegistrationInput($data));
} catch (SemanticVariableException $e) {
    $englishMessages = $e->getErrors()->getMessages('en');
    $japaneseMessages = $e->getErrors()->getMessages('ja');
}
```

## The Revolution

Semantic Variables eliminate defensive programming by making **impossible states impossible**.

The type system becomes a **domain language**—each type carries the meaning of what can exist in your business domain.

Function signatures become **documentation**:
```php
function processOrder(ProductCode $product, PaymentAmount $amount, CustomerAge $age)
{
    // The signature IS the specification
}
```

---

**Next**: Learn about [Type-Driven Metamorphosis](06-type-driven-metamorphosis.md) where objects discover their own nature.

*"Semantic Variables don't just validate data—they ensure only meaningful beings can exist."*