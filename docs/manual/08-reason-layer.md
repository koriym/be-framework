# 7. Reason Layer: Ontological Capabilities

> "Context is not decoration—it is the very condition of existence."

The Reason Layer embodies **Transcendent** forces—the contextual capabilities that shape how beings transform.

## Beyond Simple Services

Traditional dependency injection provides tools:

```php
public function __construct(
    EmailService $emailService,     // Just a tool
    DatabaseService $database       // Just a tool
) {}
```

## Ontological Capabilities

The Reason Layer provides **contextual being-capabilities**:

```php
public function __construct(
    #[Input] string $message,                    // Immanent
    #[Inject] #[English] CulturalGreeting $greeting,  // Transcendent capability
    #[Inject] #[Formal] BusinessProtocol $protocol     // Transcendent context
) {
    // Capability and context shape the transformation
}
```

## Reason Classes: Ways of Being

Reason classes are **not services**—they are contextual ways of being:

```php
namespace App\Reason;

final class CasualStyle
{
    public function format(string $message): string
    {
        return strtolower($message) . " 😊";
    }
    
    public function getGreeting(): string
    {
        return "Hey there!";
    }
}

final class FormalStyle  
{
    public function format(string $message): string
    {
        return ucfirst($message) . ".";
    }
    
    public function getGreeting(): string
    {
        return "Good day.";
    }
}
```

These are **ontological modes**—different ways of existing in specific contexts.

## Context-Driven Transformation

The same object transforms differently based on contextual capabilities:

```php
final class FormattedGreeting
{
    public readonly string $greeting;
    public readonly string $signature;
    
    public function __construct(
        #[Input] string $name,
        #[Input] string $message,
        #[Inject] StyleReason $style       // Context shapes transformation
    ) {
        $this->greeting = $style->getGreeting() . " " . $name;
        $this->signature = $style->format($message);
    }
}
```

## Cultural Context Ontologies

Applications naturally adapt to cultural contexts:

```php
final class JapaneseEtiquette
{
    public function addHonorific(string $name): string
    {
        return $name . "-san";
    }
    
    public function formatGreeting(string $message): string
    {
        return "いつもお世話になっております。" . $message;
    }
}

final class AmericanEtiquette
{
    public function addHonorific(string $name): string
    {
        return $name;  // No honorific needed
    }
}
```

## Strategy as Ontology

Unlike the Strategy pattern, Reason classes represent **ways of being**, not algorithms:

```php
interface PricingOntology
{
    public function interpretValue(Money $price): PriceCategory;
}

final class LuxuryMarketOntology implements PricingOntology
{
    // In luxury context, high price means exclusivity
}

final class MassMarketOntology implements PricingOntology  
{
    // In mass market, high price means barrier
}
```

## Multiple Contextual Capabilities

```php
final class InternationalMessage
{
    public function __construct(
        #[Input] string $recipientName,
        #[Input] string $message,
        #[Inject] CulturalEtiquette $culture,     // Cultural context
        #[Inject] CommunicationProtocol $protocol, // Communication context
        #[Inject] FormalityLevel $formality       // Formality context
    ) {
        $name = $culture->addHonorific($recipientName);
        $greeting = $culture->formatGreeting($message);
        $styled = $formality->apply($greeting);
        
        $this->content = $protocol->format($styled);
    }
}
```

## Dependency Resolution

Context-aware binding through dependency injection:

```php
$injector->bind(PaymentGateway::class)
    ->annotatedWith(Production::class)
    ->to(StripeGateway::class);
    
$injector->bind(PaymentGateway::class)
    ->annotatedWith(Testing::class)
    ->to(MockGateway::class);
```

## The Revolution

The Reason Layer transforms dependency injection from **tool provision** to **ontological context**.

Objects don't just receive services—they receive **ways of being** appropriate to their environment.

---

**Next**: Learn about [Error Handling & Validation](08-error-handling.md) where semantic exceptions preserve meaning.

*"The Reason Layer is where the world's capabilities meet the object's nature—as contextual condition for meaningful becoming."*