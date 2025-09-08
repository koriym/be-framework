# Be Framework Example

This directory contains a complete example demonstrating the Be Framework's Ontological Programming paradigm through a greeting system.

## Overview

The greeting example showcases how data transforms through continuous metamorphosis using constructor-driven transformations. Input data evolves through Being states until reaching final existence forms.

## Quick Start

```bash
cd /path/to/be-framework/v0
php example/hello-world.php
```

## Architecture

### Metamorphosis Flow

```
GreetingInput -> BeGreeting -> [FormalGreeting | CasualGreeting]
```

1. **GreetingInput**: Initial state with name and style
2. **BeGreeting**: Intermediate Being state that validates and processes
3. **FormalGreeting/CasualGreeting**: Final existence states

### Directory Structure

```
example/
├── hello-world.php          # Main demonstration script
├── Input/
│   └── GreetingInput.php    # Input entity with #[Be] attribute
├── Being/
│   ├── BeGreeting.php       # Intermediate transformation state
│   ├── FormalGreeting.php   # Final formal greeting state
│   └── CasualGreeting.php   # Final casual greeting state
├── Semantic/                # Semantic Variables for validation
│   ├── Name.php             # Name validation logic
│   ├── Style.php            # Style validation logic
│   └── Being.php            # Being validation logic
├── Exception/               # Domain-specific exceptions
│   ├── InvalidNameFormatException.php
│   └── StyleException.php
├── Tag/                     # Semantic tags for attributes
└── Reason/                  # Ontological existence reasons (Being justification)
```

## Key Concepts Demonstrated

### 1. Immutable Transformation
All objects are `readonly` with state transformation happening only through constructors:

```php
#[Be([BeGreeting::class])]
final readonly class GreetingInput
{
    public function __construct(
        public string $name,
        public string $style,
    ) {}
}
```

### 2. Branching Metamorphosis
Single input can transform into multiple possible outcomes:

```php
#[Be([FormalGreeting::class, CasualGreeting::class])]
final readonly class BeGreeting
{
    // Transformation logic in constructor
}
```

### 3. Semantic Validation
Input validation through semantic variables:

```php
// In BeGreeting constructor
public function __construct(
    #[Input, English] string $name,     // Validated by Name semantic variable
    #[Input] string $style,             // Validated by Style semantic variable
    // ...
) {}
```

### 4. Dependency Injection
External dependencies injected as transcendent factors:

```php
public function __construct(
    #[Input] string $data,              // Immanent (from previous object)
    #[Inject] SomeService $service,     // Transcendent (from DI container)
) {}
```

## Running the Example

The `hello-world.php` script demonstrates three scenarios:

### 1. Successful Formal Greeting
```php
$formalInput = new GreetingInput('Smith', 'formal');
$result = $becoming($formalInput);
// Results in FormalGreeting object
```

### 2. Successful Casual Greeting
```php
$casualInput = new GreetingInput('Alice', 'casual');
$result = $becoming($casualInput);
// Results in CasualGreeting object
```

### 3. Validation Failures
```php
$invalidInput = new GreetingInput('', 'casual');        // Empty name
$invalidInput = new GreetingInput('郡山 昭仁', 'casual');  // Non-English name
// Both trigger SemanticVariableException with Japanese error messages
```

## Expected Output

```
✅ Formal existence:
{
    "greeting": "Good day, Mr. Smith",
    "style": "formal",
    "name": "Smith"
}

✅ Casual existence:
{
    "greeting": "Hey Alice!",
    "style": "casual", 
    "name": "Alice"
}

✅ Be\Example\Exception\InvalidNameFormatException: 名前は必須です
✅ Be\Example\Exception\InvalidNameFormatException: 名前は英語で入力してください
```

## Framework Features Showcased

- **Constructor-only Logic**: All business logic resides in constructors
- **Immutable State**: `public readonly` properties ensure no mutation
- **Declarative Transformations**: `#[Be]` attributes declare transformation paths
- **Semantic Validation**: Input validation through semantic variable system
- **Multilingual Error Messages**: Error messages in multiple languages (Japanese/English)
- **Dependency Injection**: Ray.Di integration for external dependencies
- **Type Safety**: Full PHP 8.3 type system utilization

## Philosophy

This example embodies the framework's "Be, Don't Do" philosophy:
- Objects represent **states of being** rather than behaviors
- Transformations are **irreversible metamorphosis** through constructors
- All state is **immutable** and **transparent**
- Business logic is **declarative** rather than imperative

## Further Reading

- [Be Framework Documentation](../docs/README.md)
- [Ontological Programming Paper](../docs/papers/philosophy/ontological-programming-paper.md)
- [Technical Whitepaper](../docs/papers/framework/be-framework-whitepaper.md)
