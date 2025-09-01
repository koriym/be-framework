# Semantic Log Architecture for Be Framework

## Overview

The Be Framework uses **koriym/semantic-logger** to provide transparent logging of all object transformations. Each individual transformation (constructor call) is logged as a complete Open-Close pair, capturing both the intent and the result.

## Open-Close Pattern for Individual Transformations

```
MetamorphosisOpenContext (OPEN) - "About to transform UserInput using SemanticValidator"
    ↓ [Constructor execution happens here]
MetamorphosisCloseContext (CLOSE) - "UserInput became ValidatedUser with properties {...}"
```

Each transformation gets its own open/close pair:

```
UserInput → ValidatedUser → RegisteredUser → ActiveUser

1. OPEN: UserInput transformation intent
   CLOSE: ValidatedUser result + next destination
2. OPEN: ValidatedUser transformation intent  
   CLOSE: RegisteredUser result + next destination
3. OPEN: RegisteredUser transformation intent
   CLOSE: ActiveUser result + final destination
```

## MetamorphosisOpenContext (Open)
**Purpose**: Captures constructor arguments BEFORE instantiation
- **When**: Called immediately before each constructor call
- **What it captures**:
  - `fromClass`: Class being transformed from
  - `beAttribute`: The `#[Be]` attribute string (e.g., `#[Be(ValidatedUser::class)]`)
  - `immanentSources`: `#[Input]` parameter sources from previous object
  - `transcendentSources`: `#[Inject]` service types from DI container

## MetamorphosisCloseContext (Close)  
**Purpose**: Captures transformation results AFTER instantiation
- **When**: Called immediately after successful constructor completion
- **What it captures**:
  - `properties`: All public properties of the created object
  - `be`: Next destination information (SingleDestination, MultipleDestination, FinalDestination, or DestinationNotFound)

## Destination Types

### SingleDestination
Object has a single next transformation: `#[Be(SomeClass::class)]`
```php
new SingleDestination(nextClass: 'RegisteredUser');
```

### MultipleDestination  
Object has branching possibilities: `#[Be([Success::class, Failure::class])]`
```php
new MultipleDestination(possibleClasses: ['Success', 'Failure']);
```

### FinalDestination
Object has no `#[Be]` attribute - transformation chain ends here
```php
new FinalDestination(finalClass: 'ActiveUser');
```

### DestinationNotFound
Transformation failed or type matching failed
```php
new DestinationNotFound(error: 'No matching constructor found', attemptedClasses: ['UserA', 'UserB']);
```

## Real Implementation Example

```php
// User Registration Flow: UserInput → ValidatedUser → RegisteredUser → ActiveUser

// 1. OPEN: About to transform UserInput
$openId1 = $logger->open(new MetamorphosisOpenContext(
    fromClass: 'UserInput',
    beAttribute: '#[Be(ValidatedUser::class)]',
    immanentSources: ['email' => 'UserInput::email', 'age' => 'UserInput::age'],
    transcendentSources: ['validator' => 'SemanticValidator']
));

// [ValidatedUser constructor executes]

// 1. CLOSE: UserInput became ValidatedUser
$logger->close(new MetamorphosisCloseContext(
    properties: ['email' => 'user@example.com', 'age' => 25, 'isValid' => true],
    be: new SingleDestination('RegisteredUser')
), $openId1);

// 2. OPEN: About to transform ValidatedUser
$openId2 = $logger->open(new MetamorphosisOpenContext(
    fromClass: 'ValidatedUser', 
    beAttribute: '#[Be(RegisteredUser::class)]',
    immanentSources: ['email' => 'ValidatedUser::email'],
    transcendentSources: ['repository' => 'UserRepository']
));

// [RegisteredUser constructor executes]

// 2. CLOSE: ValidatedUser became RegisteredUser
$logger->close(new MetamorphosisCloseContext(
    properties: ['userId' => '123', 'email' => 'user@example.com', 'createdAt' => '2024-01-01'],
    be: new SingleDestination('ActiveUser')
), $openId2);

// 3. OPEN: About to transform RegisteredUser  
$openId3 = $logger->open(new MetamorphosisOpenContext(
    fromClass: 'RegisteredUser',
    beAttribute: '#[Be(ActiveUser::class)]',
    immanentSources: ['userId' => 'RegisteredUser::userId', 'email' => 'RegisteredUser::email'],
    transcendentSources: ['emailService' => 'EmailService']
));

// [ActiveUser constructor executes]

// 3. CLOSE: RegisteredUser became ActiveUser (final destination)
$logger->close(new MetamorphosisCloseContext(
    properties: ['userId' => '123', 'email' => 'user@example.com', 'isActive' => true],
    be: new FinalDestination('ActiveUser')  // No more transformations
), $openId3);
```

## Key Principles

### Individual Transformation Focus
Each constructor call gets its own complete open/close pair, providing granular visibility into every transformation step.

### Source Tracking  
- **immanentSources**: Maps each constructor parameter to its source property (e.g., `'email' => 'UserInput::email'`)
- **transcendentSources**: Maps injected services to their types (e.g., `'validator' => 'SemanticValidator'`)

### Destination Prediction
The close context includes next destination information, allowing the logger to predict the transformation chain's future path without executing it.

### Schema Compliance
All contexts extend `AbstractContext` and have associated JSON schemas for validation:
- `metamorphosis-open.json` - Validates MetamorphosisOpenContext
- `metamorphosis-close.json` - Validates MetamorphosisCloseContext

## Implementation Notes

### Logger Integration
The Be Framework's `Logger` class wraps `koriym/semantic-logger` and automatically:
- Extracts constructor arguments before instantiation
- Maps immanent sources from object properties
- Detects transcendent sources from injected objects
- Determines next destination from `#[Be]` attributes
- Handles error cases with `DestinationNotFound`

### Limitations
- @todo Currently only logs string-based single transformations 
- @todo Array-based branching transformations (`#[Be([A::class, B::class])]`) are skipped
- @todo Source mapping uses value equality, which may not work for complex objects

### Log Output Structure
The semantic logger produces a hierarchical JSON structure where each transformation appears as a nested open/close pair, providing complete traceability of the metamorphosis chain.