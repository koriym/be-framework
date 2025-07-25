# 3. Final Objects

Final Objects represent the destination of metamorphosis—complete, transformed beings that embody the user's actual interest. These are what the application ultimately cares about.

## Characteristics of Final Objects

**Complete Beings**: Final Objects are fully formed entities that need no further transformation for their intended purpose.

**User-Focused**: They represent what users actually want—successful operations, meaningful data, actionable results.

**Rich State**: Unlike Input Classes, Final Objects contain the full richness of transformed data.

## Examples

### Successful Outcomes
```php
final class SuccessfulOrder
{
    public readonly string $orderId;
    public readonly string $confirmationCode;
    public readonly DateTimeImmutable $timestamp;
    public readonly string $message;
    
    public function __construct(
        #[Input] Money $total,                    // Immanent from validation
        #[Input] CreditCard $card,                // Immanent from validation
        #[Inject] OrderIdGenerator $generator,    // Transcendent
        #[Inject] Receipt $receipt                // Transcendent
    ) {
        $this->orderId = $generator->generate();              // New Immanent
        $this->confirmationCode = $receipt->generate($total); // New Immanent
        $this->timestamp = new DateTimeImmutable();          // New Immanent
        $this->message = "Order confirmed: {$this->orderId}"; // New Immanent
    }
}
```

### Error States as Final Objects
```php
final class FailedOrder
{
    public readonly string $errorCode;
    public readonly string $message;
    public readonly DateTimeImmutable $timestamp;
    
    public function __construct(
        #[Input] array $errors,                   // Immanent from validation
        #[Inject] Logger $logger,                 // Transcendent
        #[Inject] ErrorCodeGenerator $generator   // Transcendent
    ) {
        $this->errorCode = $generator->generate();
        $this->message = "Order failed: " . implode(', ', $errors);
        $this->timestamp = new DateTimeImmutable();
        
        $logger->logOrderFailure($this->errorCode, $errors);  // Side effect
    }
}
```

## Final Objects vs Input Classes

| Input Classes | Final Objects |
|---------------|---------------|
| Pure identity | Rich, transformed state |
| Starting point | Destination |
| What user provides | What user receives |
| Simple structure | Complete functionality |

## Multiple Final Destinies

Objects can have multiple possible final forms, determined by their nature:

```php
// From OrderValidation's being property:
public readonly SuccessfulOrder|FailedOrder $being;

// Usage:
$order = $becoming(new OrderInput($items, $card));

if ($order->being instanceof SuccessfulOrder) {
    echo $order->being->confirmationCode;
} else {
    echo $order->being->message;  // Error message
}
```

## The Journey Complete

The path from Input to Final Object represents a complete transformation journey:

1. **Input Class**: Pure identity ("Here's what I am")
2. **Being Classes**: Transformation stages ("Here's how I change")  
3. **Final Object**: Complete result ("Here's what I became")

Users primarily care about Input (what they provide) and Final Objects (what they get back). The Being Classes in between are the framework's responsibility—the machinery of transformation that creates the bridge between intention and result.

## Natural Completion

Final Objects embody the completion of natural transformation. They don't need to "do" anything more—they simply *are* the result that was meant to emerge from the original input's encounter with the world's capabilities.