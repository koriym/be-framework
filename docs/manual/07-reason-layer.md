# 7. Reason Layer: Ontological Capabilities

> "Context is not decoration—it is the very condition of existence."

The Reason Layer embodies **Transcendent** forces in Be Framework—the contextual capabilities and environmental ontologies that shape how beings transform. Here, capability meets context to create meaningful change.

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

## Reason Classes: Contextual Ontologies

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
#[Be(FormattedGreeting::class)]
final class GreetingInput
{
    public function __construct(
        #[Input] public readonly string $name,
        #[Input] public readonly string $message
    ) {}
}

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

## Tagged Capabilities

Semantic tags specify **which contextual capability** to use:

```php
// Casual context transformation
$casualGreeting = $becoming(
    new GreetingInput("Alice", "thanks"),
    new BecomingArguments([
        StyleReason::class => new CasualStyle()    // Casual ontological context
    ])
);

// Formal context transformation  
$formalGreeting = $becoming(
    new GreetingInput("Alice", "thanks"),
    new BecomingArguments([
        StyleReason::class => new FormalStyle()    // Formal ontological context
    ])
);
```

## Hierarchical Reason Architecture

### Domain-Specific Ontologies

```php
namespace App\Reason\Communication;

final class EmailProtocol
{
    public function format(string $content): string
    {
        return "Subject: Notification\n\n" . $content;
    }
}

final class SmsProtocol
{
    public function format(string $content): string
    {
        return substr($content, 0, 160);  // SMS length limit
    }
}
```

### Cultural Context Ontologies

```php
namespace App\Reason\Culture;

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
    
    public function formatGreeting(string $message): string
    {
        return "Hope you're doing well. " . $message;
    }
}
```

## Complex Contextual Interactions

Multiple contextual capabilities can interact:

```php
final class InternationalMessage
{
    public readonly string $content;
    public readonly string $signature;
    
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
        $this->signature = $protocol->getSignature();
    }
}
```

## Reason Layer Patterns

### Strategy as Ontology

Unlike the Strategy pattern, Reason classes represent **ways of being**, not algorithms:

```php
// Not "how to calculate" but "what it means to be expensive/cheap"
interface PricingOntology
{
    public function interpretValue(Money $price): PriceCategory;
    public function suggestAction(PriceCategory $category): Action;
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

### Context as Capability

```php
final class PaymentProcessing
{
    public function __construct(
        #[Input] Money $amount,
        #[Input] PaymentMethod $method,
        #[Inject] #[Production] PaymentGateway $gateway,    // Production context
        #[Inject] #[Encrypted] SecurityProtocol $security   // Security context
    ) {
        // Production + Security context shapes the transformation
    }
}
```

## Dependency Resolution

The Reason Layer integrates with dependency injection:

```php
// Module configuration
$injector->bind(StyleReason::class)->to(CasualStyle::class)->in(Scope::SINGLETON);
$injector->bind(CulturalEtiquette::class)->to(JapaneseEtiquette::class);

// Context-aware binding
$injector->bind(PaymentGateway::class)
    ->annotatedWith(Production::class)
    ->to(StripeGateway::class);
    
$injector->bind(PaymentGateway::class)
    ->annotatedWith(Testing::class)
    ->to(MockGateway::class);
```

## Benefits of Reason Layer

### 1. **Contextual Polymorphism**
Same transformation, different contextual meaning.

### 2. **Ontological Clarity**
Capabilities represent **ways of being**, not just doing.

### 3. **Environmental Awareness**
Objects transform according to their environment's capabilities.

### 4. **Cultural Sensitivity**
Applications naturally adapt to cultural contexts.

### 5. **Testable Contexts**
Different contexts can be easily swapped for testing.

## Best Practices

### Naming Conventions
- Use domain language: `FormalStyle` not `FormalFormatter`
- Represent being, not doing: `SecurityProtocol` not `SecurityValidator`
- Include context: `JapaneseEtiquette` not `Etiquette`

### Reason Class Design
- Focus on **ontological meaning**, not mechanical function
- Embody **contextual ways of being**
- Keep **context-specific logic** contained

### Integration Patterns
- Use **semantic tags** for context specification
- Design for **compositional contexts** (multiple Reason classes)
- Maintain **separation** between Immanent data and Transcendent capabilities

---

**Next**: Learn about [Error Handling & Validation](08-error-handling.md) where semantic exceptions preserve meaning through failure.

*"The Reason Layer is where the world's capabilities meet the object's nature—not as external force, but as contextual condition for meaningful becoming."*