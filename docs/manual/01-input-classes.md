# 1. Input Classes

Input Classes are the starting point of every transformation in Be Framework. They carry pure **Immanent** nature—what something already is, its essential identity.

## Basic Structure

```php
#[Be(UserProfile::class)]  // Metamorphosis destiny
final class UserInput
{
    public function __construct(
        public readonly string $name,     // Immanent
        public readonly string $email     // Immanent
    ) {}
}
```

## Key Characteristics

**Pure Identity**: Input Classes contain only what the object fundamentally *is*—no external dependencies, no complex logic.

**Metamorphosis Destiny**: The `#[Be()]` attribute declares what this input will become.

**Readonly Properties**: All data is immutable, representing fixed identity that will transform, not mutate.

## Examples

### Simple Data Input
```php
#[Be(OrderCalculation::class)]
final class OrderInput
{
    public function __construct(
        public readonly array $items,        // Immanent
        public readonly string $currency     // Immanent
    ) {}
}
```

### Complex Structured Input
```php
#[Be(PaymentProcessing::class)]
final class PaymentInput
{
    public function __construct(
        public readonly Money $amount,           // Immanent
        public readonly CreditCard $card,        // Immanent
        public readonly Address $billing         // Immanent
    ) {}
}
```

## The Role of Immanent

In Input Classes, everything is **Immanent**—the object's inherent nature that it carries forward into transformation. There are no **Transcendent** forces here; those come later in Being Classes.

This represents the "self" part of the transformation equation:
**Immanent + Transcendent → New Immanent**

Input Classes provide the foundation—the "what already is" that will encounter the world and become something new.