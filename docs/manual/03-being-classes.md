# 2. Being Classes

Being Classes are where transformation happens. They receive **Immanent** identity from previous stages and **Transcendent** forces from the world, creating new forms of existence.

## Basic Structure

```php
final class UserProfile
{
    public readonly string $displayName;
    public readonly bool $isValid;
    
    public function __construct(
        #[Input] string $name,                    // Immanent
        #[Input] string $email,                   // Immanent
        #[Inject] NameFormatter $formatter,       // Transcendent
        #[Inject] EmailValidator $validator       // Transcendent
    ) {
        $this->displayName = $formatter->format($name);     // New Immanent
        $this->isValid = $validator->validate($email);      // New Immanent
    }
}
```

## The Transformation Pattern

Every Being Class follows the same ontological pattern:

**Immanent** (`#[Input]`) + **Transcendent** (`#[Inject]`) → **New Immanent**

- **Immanent factors**: What the object inherits from its previous form
- **Transcendent factors**: External capabilities and context provided by the world
- **New Immanent**: The transformed being that emerges from this interaction

## Constructor as Workshop

The constructor is where metamorphosis occurs. It's a complete workshop where:

1. Identity meets capability
2. Transformation logic resides
3. New immutable being emerges

```php
final class OrderCalculation
{
    public readonly Money $subtotal;
    public readonly Money $tax;
    public readonly Money $total;
    
    public function __construct(
        #[Input] array $items,                    // Immanent
        #[Input] string $currency,                // Immanent
        #[Inject] PriceCalculator $calculator,    // Transcendent
        #[Inject] TaxService $taxService          // Transcendent
    ) {
        $this->subtotal = $calculator->calculateSubtotal($items, $currency);
        $this->tax = $taxService->calculateTax($this->subtotal);
        $this->total = $this->subtotal->add($this->tax);     // New Immanent
    }
}
```

## Bridging to Final Objects

Being Classes often serve as bridges, preparing data for final transformation:

```php
#[Be([SuccessfulOrder::class, FailedOrder::class])]  // Multiple destinies
final class OrderValidation
{
    public readonly bool $isValid;
    public readonly array $errors;
    public readonly SuccessfulOrder|FailedOrder $being;  // Being Property
    
    public function __construct(
        #[Input] Money $total,                    // Immanent
        #[Input] CreditCard $card,                // Immanent
        #[Inject] PaymentGateway $gateway         // Transcendent
    ) {
        $result = $gateway->validate($card, $total);
        $this->isValid = $result->isValid();
        $this->errors = $result->getErrors();
        
        // Self-determination of destiny
        $this->being = $this->isValid 
            ? new SuccessfulOrder($total, $card)
            : new FailedOrder($this->errors);
    }
}
```

## Natural Flow

Being Classes don't "do" things—they naturally become what they're meant to be through the interaction of their nature with the world's capabilities. This embodies the principle of natural transformation without forced control.