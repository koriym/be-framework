# Type-Driven Metamorphosis Specification

This document specifies the exact behavior of Type-Driven Metamorphosis in Be Framework, eliminating common misconceptions about how array-based `#[Be]` attributes function.

## Core Principles: Hybrid Type-Driven and Exception-Driven Approach

**Be Framework implements a hybrid approach combining Type-Driven selection with Exception-Driven fallback for maximum flexibility and robustness.**

### Primary Mechanism: Type Signature Matching

**Type-Driven Metamorphosis operates on constructor type signatures as the primary selection mechanism.**

### Rule 1: Constructor Signature Matching

When `#[Be([ClassA::class, ClassB::class])]` is specified, the framework:

1. **Examines constructor signatures** of each class in array order
2. **Selects the FIRST class** whose constructor signature matches available parameters
3. **Instantiates that class immediately** - no further classes are considered

### Rule 2: Array Order Determines Priority

```php
#[Be([FormalGreeting::class, CasualGreeting::class])]  // Order matters!
```

- `FormalGreeting` is checked first
- If its constructor signature matches, it is selected
- `CasualGreeting` is **never evaluated** if `FormalGreeting` matches

### Rule 3: Identical Signatures = Only First is Selected

❌ **WRONG - This will NEVER select `CasualGreeting`:**

```php
final class FormalGreeting
{
    public function __construct(
        #[Input] string $name,
        #[Input] string $style    // Same signature as CasualGreeting
    ) {
        if ($style !== 'formal') {
            throw new InvalidArgumentException('Not formal');  // This exception is IRRELEVANT
        }
        // ...
    }
}

final class CasualGreeting
{
    public function __construct(
        #[Input] string $name,
        #[Input] string $style    // Same signature - will NEVER be reached
    ) {
        // This constructor will NEVER execute
    }
}
```

**Result**: `FormalGreeting` always selected because signatures are identical.

## Correct Implementation Patterns

### Pattern 1: Distinct Type Parameters

✅ **CORRECT - Different parameter types enable proper selection:**

```php
#[Be([FormalGreeting::class, CasualGreeting::class])]
final class GreetingInput
{
    public function __construct(
        public readonly string $name,
        public readonly FormalStyle|CasualStyle $style  // Union type input
    ) {}
}

final class FormalGreeting
{
    public function __construct(
        #[Input] string $name,
        #[Input] FormalStyle $style  // Matches only FormalStyle
    ) {
        $this->greeting = "Good day, Mr./Ms. {$name}";
    }
    
    public readonly string $greeting;
}

final class CasualGreeting
{
    public function __construct(
        #[Input] string $name,
        #[Input] CasualStyle $style  // Matches only CasualStyle
    ) {
        $this->greeting = "Hey {$name}!";
    }
    
    public readonly string $greeting;
}
```

**How it works**:
- If input has `FormalStyle` → `FormalGreeting` constructor matches
- If input has `CasualStyle` → `FormalGreeting` constructor fails to match → `CasualGreeting` is tried and matches

### Pattern 2: Different Parameter Counts

✅ **CORRECT - Different parameter counts:**

```php
final class SimpleUser
{
    public function __construct(
        #[Input] string $email
    ) {
        $this->email = $email;
    }
}

final class UserWithProfile
{
    public function __construct(
        #[Input] string $email,
        #[Input] UserProfile $profile  // Additional parameter
    ) {
        $this->email = $email;
        $this->profile = $profile;
    }
}
```

This is the **preferred pattern** because:
- Type decision is explicit and clear
- Both classes in array have the same constructor signature (which is fine)
- The actual type selection happens through the `$being` property

Note: In this pattern, the framework instantiates the host class and determines the final type from `$being`. Candidate constructor signatures are not used for selection; instead, your constructor decides which concrete type to expose.
## Common Misconceptions

### ❌ Misconception 1: "Exceptions determine type selection"

**FALSE**: Exceptions thrown during constructor execution do NOT cause the framework to try the next class in the array. If a constructor matches by signature, it will be executed regardless of whether it throws exceptions.

### ❌ Misconception 2: "Runtime validation determines type selection"

**FALSE**: The content of constructor logic (validation, business rules) does NOT affect type selection. Type selection happens at the signature level before constructor execution.

### ❌ Misconception 3: "String parameters with different validation are sufficient"

**FALSE**: Two constructors with `string $style` parameters cannot be distinguished by the framework, even if they validate different string values.

## Implementation Notes

### Framework Behavior

1. **Signature Analysis**: Framework analyzes constructor parameter types using reflection
2. **Type Compatibility**: Framework checks if available input data can satisfy the constructor signature
3. **First Match Wins**: Once a compatible constructor is found, that class is instantiated immediately
4. **No Backtracking**: If constructor execution fails, the framework does NOT try alternative classes

### Testing Type-Driven Metamorphosis

When testing type-driven systems, verify the TYPE of the result, not the behavior:

```php
public function testUserValidationBecomesValidUser(): void
{
    $validation = new UserValidation('valid@example.com', $mockValidator);
    
    // Test the TYPE, not the behavior
    $this->assertInstanceOf(ValidUser::class, $validation->being);
}
```

## Summary

Type-Driven Metamorphosis is a **compile-time type matching system**, not a **runtime exception-handling system**. Success depends on:

1. **Distinct constructor signatures** for each class in the array
2. **Proper type design** using value objects, union types, or the `$being` property pattern  
3. **Understanding that array order matters** for signature matching priority

When in doubt, prefer the **`$being` property pattern** for explicit, clear type determination within the constructor itself.

## Exception-Driven Fallback Mechanism

**In addition to type signature matching, Be Framework implements Exception-Driven Fallback as a secondary mechanism.**

### Current Default Behavior

When `#[Be([ClassA::class, ClassB::class, ClassC::class])]` is specified:

1. **Type signature matching** attempts to select the appropriate class
2. **If constructor execution throws any exception**, the framework automatically tries the next class in the array
3. **This continues until a class succeeds** or all classes are exhausted
4. **If all classes fail**, a `TypeMatchingFailure` exception is thrown with detailed error information

```php
// Current behavior - Exception-Driven Fallback enabled
#[Be([PremiumUser::class, RegularUser::class, GuestUser::class])]
final class UserRegistration {
    public readonly string $email;
    public readonly ?string $creditCard;
}

final class PremiumUser {
    public function __construct(
        #[Input] string $email,
        #[Input] ?string $creditCard,
        PaymentValidator $validator
    ) {
        if (!$creditCard) {
            throw new InvalidArgumentException('Premium requires credit card');
        }
        if (!$validator->isValidCard($creditCard)) {
            throw new InvalidArgumentException('Invalid credit card');
        }
        // If exceptions occur, framework automatically tries RegularUser
    }
}

final class RegularUser {
    public function __construct(
        #[Input] string $email,
        #[Input] ?string $creditCard
    ) {
        // Fallback option - less strict requirements
        // If this also fails, framework tries GuestUser
    }
}
```

### Philosophical Alignment

This Exception-Driven Fallback aligns with Be Framework's core philosophy of **"natural metamorphosis"**:
- Objects attempt to become their most suitable form
- If one form is not viable, they naturally adapt to try alternative forms
- This mirrors biological metamorphosis where environmental conditions determine the final form

## Design Decision: Maintaining Simplicity

### Current Approach (Implemented)
- **Exception-Driven Fallback is always enabled** when arrays are used
- **No configuration options** for exception handling behavior
- **Consistent behavior** across all `#[Be]` attributes

### Future Consideration (Not Currently Implemented)
A `strict: true` option was considered but **deliberately not implemented at this time** based on the following design philosophy:

```php
// Potential future feature - NOT currently available
#[Be([AdminUser::class, RegularUser::class], strict: true)]
// This would disable exception fallback for type-safety critical scenarios
```

**Reasons for current omission**:
1. **Simplicity preservation**: Maintaining Be Framework's core principle of natural, unopinionated behavior
2. **Philosophical consistency**: Exception-Driven Fallback embodies the "natural adaptation" metaphor
3. **Staged approach**: Waiting for real-world usage patterns before adding complexity
4. **Learning cost reduction**: Avoiding cognitive load from additional configuration options

### When Strict Mode Might Be Added

Future implementation would be considered if:
- **Clear use cases emerge** requiring immediate failure on exceptions
- **Security-critical scenarios** demand no fallback behavior  
- **Community consensus develops** around the need for strict typing
- **Debugging scenarios** require exception transparency over fallback

The potential naming would likely be `strict: true` rather than `continueOnException: false` to better align with Be Framework's philosophical approach.