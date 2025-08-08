# Be Framework Concept

> "Be, Don't Do" — When programming aligns with the principle of Wu Wei (無為)

Be Framework is a PHP framework that implements the Ontological Programming paradigm, where data transformation occurs through pure constructor-driven metamorphosis.

## Philosophy

Be Framework emerged from a simple yet profound question: What if we programmed by defining what can exist, rather than what should happen?

Building upon the philosophical foundations of Ray.Di's dependency injection pattern, Be Framework treats all data transformations as metamorphosis—continuous becoming through constructor injection. Each object accepts its inevitable premises and emerges as a whole new being through the process of becoming.

In Be Framework, every transformation arises from the interaction of:

**Immanent factors** — what the object already is (its identity)

**Transcendent factors** — what the world provides (context, capability)

This mirrors how beings in the world come into meaningful existence: not by internal properties alone, but by encountering something beyond themselves.

## Core Concepts

### Being Classes

Every class in Be Framework is a Being Class—a self-contained, immutable stage of existence and transformation:

```php
#[Be(Greeting::class)]  // Metamorphosis destiny
final class NameInput
{
    public function __construct(
        public readonly string $name  // Immanent
    ) {}
}

final class Greeting
{
    public readonly string $message;
    
    public function __construct(
        #[Input] string $name,                // Immanent
        #[Inject] WorldGreeting $greeting     // Transcendent
    ) {
        $this->message = "{$greeting->text} {$name}";  // New Immanent
    }
}
```

### The Becoming Execution

```php
// Execute metamorphosis
$becoming = new Becoming($injector);
$finalObject = $becoming(new NameInput('world'));

echo $finalObject->message; // hello world
```

## Key Principles

1. **"Be, Don't Do"**: Program by defining existence states, not action sequences
2. **Metamorphosis over Mutation**: Objects transform into new beings rather than changing state
3. **Being-Oriented Programming**: Domain and time cannot be separated - objects exist temporally
4. **Temporal Existence**: You can't die before you're born - objects follow natural life cycles
5. **Immanent + Transcendent = New Immanent**: Every transformation combines internal essence with external capability

## Documentation

### Foundation
**[Academic Papers](docs/papers/)** - Theoretical and philosophical foundations  
**[Philosophical Foundation](docs/reference/)** - Influences and insights

### Practical Guides  
**[Be Framework Manual](docs/manual/index.md)** - Tutorial-level implementation guide  
**[Complete Documentation Guide](docs/README.md)** - Comprehensive reading paths  
**[AI-Powered Learning](docs/study/README.md)** - Interactive exploration ([日本語](docs/study/README-ja.md))

## Examples

- **[User Registration](examples/user-registration/)**  
  Complete implementation demonstrating Type-Driven Metamorphosis and constructor validation

## Audio Content

**[Be Framework Podcast Series](docs/study/podcast/)**  
AI-generated audio introductions exploring ontological programming concepts and philosophical foundations (English, ~30 minutes each)

## Key Paradigm Shifts

Be Framework represents fundamental shifts in how we think about programs:

- From **doing** to **being**
- From **instructions** to **transformations**
- From **mutable state** to **immutable existence**
- From **complex lifecycles** to **simple metamorphosis**

## Status

Be Framework is currently in the conceptual and early implementation phase. The ideas presented here emerged from deep dialogues about the nature of programming and represent a new approach to building applications.

## The Journey

Be Framework emerged from recognizing universal patterns in data transformation. What started as a specific solution for HTTP data processing revealed deeper principles about how objects transform through constructor injection. 

These concepts emerged from decades of resource-oriented architecture framework development by Akihito Koriyama, along with extensive experience in OOP, REST, and diverse software engineering disciplines. All core concepts, ideas, and patterns presented here originated from this practical foundation. AI assisted in the evolution of these thoughts and handled all documentation, but the fundamental paradigms and design patterns were conceived through years of hands-on software architecture and development.


---

*"Be Framework opens PHP to the natural process of becoming—objects that flow, transform, and emerge as their true selves."*
