# Semantic Log Architecture for Be Framework

## Overview

The Be Framework uses **koriym/semantic-logger** to provide transparent logging of all object transformations. Each individual transformation (constructor call) is logged as a complete Open-Close pair, capturing both the intent and the result.

## Open-Close Pattern for Individual Transformations

```text
BeingOpenContext (OPEN)              - "About to transform UserInput using SemanticValidator"
    ↓ [Constructor execution happens here]
BeingCloseContext / BeingFinalCloseContext / BeingErrorCloseContext (CLOSE)
                                     - "UserInput became ValidatedUser with properties {...}"
```

The open context comes in two forms, picked by whether the target class declares a `#[Be]` attribute:

- `BeingOpenContext` — target carries `#[Be]`, the metamorphosis will continue.
- `BeingFinalOpenContext` — target has no `#[Be]`, this step produces the terminal being.

The close context comes in three forms, picked by the result state:

- `BeingCloseContext` — result has a `#[Be]` attribute; pipeline will continue.
- `BeingFinalCloseContext` — result has no `#[Be]`; chain ends here.
- `BeingErrorCloseContext` — constructor threw; open-close could not complete.

Each transformation gets its own open/close pair:

```text
UserInput → ValidatedUser → RegisteredUser → ActiveUser

1. OPEN: UserInput transformation intent        (BeingOpenContext)
   CLOSE: ValidatedUser result                  (BeingCloseContext)
2. OPEN: ValidatedUser transformation intent    (BeingOpenContext)
   CLOSE: RegisteredUser result                 (BeingCloseContext)
3. OPEN: RegisteredUser transformation intent   (BeingFinalOpenContext)
   CLOSE: ActiveUser result                     (BeingFinalCloseContext)
```

## BeingOpenContext / BeingFinalOpenContext (Open)
**Purpose**: Captures constructor arguments BEFORE instantiation
- **When**: Called immediately before each constructor call
- **What it captures**:
  - `from`:   Class being transformed from
  - `be`:     Target class FQCN
  - `input`:  `#[Input]` parameter sources from previous object
  - `inject`: `#[Inject]` service types from DI container

## BeingCloseContext / BeingFinalCloseContext / BeingErrorCloseContext (Close)
**Purpose**: Captures transformation results AFTER instantiation
- **When**: Called immediately after successful constructor completion, or after a thrown exception
- **Success close captures**:
  - `prop`:   All public properties of the created object
  - `being` / `final`: FQCN of the resulting being (new-becoming or terminal)
- **Error close captures**:
  - `error`:   Exception class name
  - `message`: Exception message

## Chain wrapper

Each full metamorphosis chain is wrapped in a `BecomingOpenContext` / `BecomingCloseContext`
pair. These are emitted once per top-level `Becoming::__invoke` call, giving the log a
single root with every step-level open/close as sibling children.

## Real Implementation Example

```php
// User Registration Flow: UserInput → ValidatedUser → RegisteredUser → ActiveUser

// 1. OPEN: About to transform UserInput
$openId1 = $logger->open(new BeingOpenContext(
    from:   'UserInput',
    be:     'ValidatedUser',
    input:  ['email' => 'UserInput::email', 'age' => 'UserInput::age'],
    inject: ['validator' => 'SemanticValidator'],
));

// [ValidatedUser constructor executes]

// 1. CLOSE: UserInput became ValidatedUser
$logger->close(new BeingCloseContext(
    being: 'ValidatedUser',
    prop:  ['email' => 'user@example.com', 'age' => 25, 'isValid' => true],
), $openId1);

// 2. OPEN: About to transform ValidatedUser
$openId2 = $logger->open(new BeingOpenContext(
    from:   'ValidatedUser',
    be:     'RegisteredUser',
    input:  ['email' => 'ValidatedUser::email'],
    inject: ['repository' => 'UserRepository'],
));

// [RegisteredUser constructor executes]

// 2. CLOSE: ValidatedUser became RegisteredUser
$logger->close(new BeingCloseContext(
    being: 'RegisteredUser',
    prop:  ['userId' => '123', 'email' => 'user@example.com', 'createdAt' => '2024-01-01'],
), $openId2);

// 3. OPEN: About to transform RegisteredUser (terminal target — no #[Be])
$openId3 = $logger->open(new BeingFinalOpenContext(
    from:   'RegisteredUser',
    be:     'ActiveUser',
    input:  ['userId' => 'RegisteredUser::userId', 'email' => 'RegisteredUser::email'],
    inject: ['emailService' => 'EmailService'],
));

// [ActiveUser constructor executes]

// 3. CLOSE: RegisteredUser became ActiveUser (final being)
$logger->close(new BeingFinalCloseContext(
    final: 'ActiveUser',
    prop:  ['userId' => '123', 'email' => 'user@example.com', 'isActive' => true],
), $openId3);
```

## Key Principles

### Individual Transformation Focus
Each constructor call gets its own complete open/close pair, providing granular visibility into every transformation step.

### Source Tracking
- **input**:  Maps each `#[Input]` constructor parameter to its source property (e.g., `'email' => 'UserInput::email'`)
- **inject**: Maps `#[Inject]` parameters to their resolved service class (e.g., `'validator' => 'SemanticValidator'`)

### Schema Compliance
All contexts extend `AbstractContext` and have associated JSON schemas for validation:
- `being-open.json`        — Validates `BeingOpenContext`
- `being-final-open.json`  — Validates `BeingFinalOpenContext`
- `being-close.json`       — Validates `BeingCloseContext`
- `being-final-close.json` — Validates `BeingFinalCloseContext`
- `being-error-close.json` — Validates `BeingErrorCloseContext`
- `becoming-open.json`     — Validates `BecomingOpenContext` (chain wrapper)
- `becoming-close.json`    — Validates `BecomingCloseContext` (chain wrapper)

## Implementation Notes

### Logger Integration
The Be Framework's `Logger` class wraps `koriym/semantic-logger` and automatically:
- Receives resolved constructor arguments from `Being::performSingleTransformation`
- Maps immanent sources (`#[Input]`) by walking the target constructor
- Detects transcendent sources (`#[Inject]`) from resolved argument objects
- Picks `BeingOpenContext` vs `BeingFinalOpenContext` by reflecting on the target's `#[Be]` attribute
- Picks the close context by reflecting on the produced result's `#[Be]` attribute

Argument resolution runs **before** `logger->open`. If resolution throws
(e.g. `SemanticVariableException`, `UnbecomingException`), no span is opened —
a candidate that cannot open its span should not leave a ghost span behind.

### Log Output Structure
The semantic logger produces a hierarchical JSON structure where each transformation appears as a nested open/close pair, providing complete traceability of the metamorphosis chain.
