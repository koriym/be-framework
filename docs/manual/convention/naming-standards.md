# Be Framework Naming Standards

> Code as philosophy: names that reflect existence, not actions

This document establishes naming conventions that align with Be Framework's ontological programming principles, ensuring code that expresses **being** rather than **doing**.

## Core Philosophy

**"Objects don't do things—they become what they are meant to be"**

Our naming reflects this fundamental shift from imperative to existential thinking:
- From **action-oriented** names → **existence-oriented** names
- From **what it does** → **what it is**
- From **controlling** → **being**

## Class Naming Patterns

### Input Classes
**Pattern**: `{Domain}Input`
**Purpose**: Pure data containers representing the starting point of metamorphosis

```php
// ✅ Correct
final class UserInput
final class OrderInput  
final class DataInput
final class PaymentInput

// ❌ Avoid
final class UserData          // Too generic
final class CreateUserRequest // Action-oriented
final class UserCommand       // Imperative thinking
```

### Being Classes
**Pattern**: `Being{Domain}` or `{Domain}Being`
**Purpose**: Intermediate transformation stages where objects discover their nature

```php
// ✅ Correct - Being prefix (recommended)
final class BeingUser
final class BeingOrder
final class BeingData
final class BeingPayment

// ✅ Acceptable - Being suffix
final class UserBeing
final class OrderBeing

// ❌ Avoid
final class UserValidator     // Action-oriented
final class OrderProcessor    // What it does, not what it is
final class DataTransformer   // Imperative thinking
```

### Final Objects
**Pattern**: Domain-specific result names expressing final state
**Purpose**: Complete transformed beings representing successful completion

```php
// ✅ Correct - State of being
final class ValidatedUser
final class ProcessedOrder
final class Success
final class Failure
final class ApprovedLoan
final class RejectedApplication

// ❌ Avoid  
final class UserResponse      // Implementation detail
final class OrderResult       // Generic
final class ProcessingOutput  // Action-oriented
```

## Property Naming

### Being Property
**Pattern**: `public readonly {Type1}|{Type2} $being;`
**Purpose**: Carries the object's destiny through union types

```php
// ✅ Correct
public readonly Success|Failure $being;
public readonly ValidUser|InvalidUser $being;
public readonly ApprovedLoan|RejectedLoan $being;

// ❌ Avoid
public readonly mixed $result;      // Not type-specific
public readonly object $outcome;    // Too generic
public readonly array $data;        // Action-oriented
```

### Immanent Properties
**Pattern**: Descriptive names reflecting inherent identity
**Purpose**: What the object already is

```php
// ✅ Correct
public readonly string $email;
public readonly Money $amount;
public readonly UserId $userId;
public readonly \DateTimeImmutable $timestamp;

// ❌ Avoid
public readonly string $inputEmail;    // Redundant prefix
public readonly Money $requestAmount;  // Action-oriented
```

## Parameter Naming

### Constructor Parameters
**Pattern**: Match property names for Immanent, descriptive for Transcendent

```php
// ✅ Correct
public function __construct(
    #[Input] string $email,              // Immanent - matches property
    #[Input] Money $amount,              // Immanent - matches property  
    #[Inject] EmailValidator $validator, // Transcendent - capability
    #[Inject] PaymentGateway $gateway    // Transcendent - external service
) {}

// ❌ Avoid
public function __construct(
    #[Input] string $userEmail,          // Different from property name
    #[Input] Money $inputAmount,         // Redundant prefix
    #[Inject] object $emailChecker,      // Not descriptive
    #[Inject] mixed $paymentService      // Not type-specific
) {}
```

## Attribute Usage

### Be Attribute
**Pattern**: `#[Be(DestinyClass::class)]` or `#[Be([Option1::class, Option2::class])]`

```php
// ✅ Single destiny
#[Be(BeingUser::class)]
final class UserInput

// ✅ Multiple destinies  
#[Be([ValidatedUser::class, InvalidUser::class])]
final class BeingUser

// ❌ Avoid
#[Be(UserProcessor::class)]    // Action-oriented
#[Be(HandleUser::class)]       // Imperative
```

### Input/Inject Comments
**Pattern**: Always include philosophical comments

```php
// ✅ Correct
public function __construct(
    #[Input] string $email,                // Immanent
    #[Inject] EmailValidator $validator    // Transcendent
) {}

// ❌ Missing philosophy
public function __construct(
    #[Input] string $email,
    #[Inject] EmailValidator $validator
) {}
```

## Domain-Specific Examples

### E-commerce Domain
```php
// Input → Being → Final
ProductInput → BeingProduct → [ValidProduct, InvalidProduct]
OrderInput → BeingOrder → [ProcessedOrder, FailedOrder]  
PaymentInput → BeingPayment → [SuccessfulPayment, DeclinedPayment]
```

### User Management Domain
```php
// Input → Being → Final  
UserInput → BeingUser → [RegisteredUser, ConflictingUser]
LoginInput → BeingLogin → [AuthenticatedUser, FailedAuthentication]
ProfileInput → BeingProfile → [UpdatedProfile, InvalidProfile]
```

### Data Processing Domain
```php
// Input → Being → Final
DataInput → BeingData → [ProcessedData, CorruptedData]
FileInput → BeingFile → [ValidatedFile, InvalidFile]
ConfigInput → BeingConfig → [LoadedConfig, MalformedConfig]
```

## Anti-Patterns to Avoid

### Imperative Naming
```php
// ❌ Action-oriented
ProcessUser, ValidateOrder, TransformData
CreatePayment, HandleRequest, ExecuteCommand

// ✅ Being-oriented  
BeingUser, BeingOrder, BeingData
BeingPayment, BeingRequest, BeingCommand
```

### Generic Naming
```php
// ❌ Too generic
Handler, Processor, Manager, Service, Util

// ✅ Specific and meaningful
BeingUser, ValidatedOrder, ProcessedPayment
```

### Technical Implementation Details
```php
// ❌ Implementation-focused
UserDTO, OrderVO, PaymentPOJO, DataObject

// ✅ Domain-focused
UserInput, BeingOrder, ProcessedPayment
```

## Naming Checklist

Before naming any class, ask:

1. **Existence Question**: "What does this object *be* rather than *do*?"
2. **Stage Question**: "What stage of metamorphosis does this represent?"
3. **Philosophy Question**: "Does this name reflect ontological thinking?"
4. **Clarity Question**: "Will developers understand the object's nature?"
5. **Consistency Question**: "Does this follow our established patterns?"

## Evolution of Names

As your understanding of the domain deepens, names may evolve:

```php
// Initial understanding
UserValidator → 

// Deeper understanding  
BeingUser →

// Full ontological clarity
BeingUser // with clear Immanent/Transcendent distinction
```

---

*"In Be Framework, names are not labels—they are declarations of existence. Choose them as carefully as you would choose words in poetry, for they shape how we think about the reality we create."*