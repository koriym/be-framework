# Coding Guide

## Exception Design Philosophy

### Specific Domain Exceptions

Create specific exception classes for each domain problem instead of throwing generic `DomainException`.

**❌ Avoid:**
```php
public function validateAge(int $age): void
{
    if ($age < 0) {
        throw new DomainException("Age cannot be negative: {$age}");
    }
}
```

**✅ Prefer:**
```php
public function validateAge(int $age): void
{
    if ($age < 0) {
        throw new NegativeAgeException($age);
    }
}
```

### Exception Class Structure

```php
#[Message([
    'en' => 'Age cannot be negative: {age} years old',
    'ja' => '年齢は負の値にできません: {age}歳',
    'es' => 'La edad no puede ser negativa: {age} años',
])]
final class NegativeAgeException extends DomainException
{
    public function __construct(
        public readonly int $age
    ) {
        parent::__construct("Age cannot be negative: {$age} years old");
    }
}
```

### Benefits

- **Type Safety**: `catch (NegativeAgeException $e)` - specific exception handling
- **Structured Data**: `$e->age` - direct access to error context
- **Clear Parameters**: Constructor shows exactly what data is needed
- **Internationalization**: `#[Message]` attribute for multi-language support
- **Domain Expression**: Business rules clearly expressed in code

### Framework Examples

See `src/Exception/` for consistent patterns:
- `ConflictingParameterAttributes` - Parameter attribute conflicts
- `MissingParameterAttribute` - Required attributes missing  
- `TypeMatchingFailure` - Type matching failures
- `ScalarParameterRequiresNamed` - Scalar parameter naming requirements

*Very semantic, very romantic.* 💕