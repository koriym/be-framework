# 6. Type-Driven Metamorphosis

> "The object knows its own nature. We merely create the conditions for its becoming."

Type-Driven Metamorphosis represents the deepest transformation in Be Framework—where objects **discover their own destiny** through the nature of their being. No external force determines what they become; their essence chooses their path.

## Beyond Predefined Paths

Traditional metamorphosis follows fixed routes:

```php
#[Be(UserProfile::class)]  // Single destiny
final class UserInput
{
    // Always becomes UserProfile, regardless of content
}
```

## Self-Determining Beings

Type-Driven Metamorphosis allows objects to **choose their own becoming**:

```php
#[Be([ActiveUser::class, InactiveUser::class])]  // Multiple possibilities
final class UserValidation
{
    public readonly ActiveUser|InactiveUser $being;  // Being Property
    
    public function __construct(
        #[Input] string $email,
        #[Input] DateTime $lastLogin,
        #[Inject] UserRepository $repository
    ) {
        $user = $repository->findByEmail($email);
        $daysSinceLogin = $lastLogin->diff(new DateTime())->days;
        
        // Self-determination of destiny
        $this->being = $daysSinceLogin < 30 
            ? new ActiveUser($user->id, $email, $lastLogin)
            : new InactiveUser($user->id, $email, $lastLogin);
    }
}
```

## The Being Property

The **Being Property** is where self-determination manifests. It must:

- Be a **union type** of all possible destinations
- Be named exactly `being`
- Contain the object's chosen destiny

```php
public readonly SuccessfulPayment|FailedPayment $being;
```

## Natural Branching

Objects choose their path based on their **inner nature**, not external conditions:

```php
#[Be([ChildAccount::class, AdultAccount::class, SeniorAccount::class])]
final class AgeBasedAccount
{
    public readonly ChildAccount|AdultAccount|SeniorAccount $being;
    
    public function __construct(
        #[Input] int $age,
        #[Input] string $name,
        #[Inject] AccountFactory $factory
    ) {
        // Age determines nature, nature determines becoming
        $this->being = match (true) {
            $age < 18 => $factory->createChild($name, $age),
            $age < 65 => $factory->createAdult($name, $age),
            default => $factory->createSenior($name, $age)
        };
    }
}
```

## Complex Decision Patterns

### Business Rule Driven

```php
#[Be([PremiumOrder::class, StandardOrder::class, BulkOrder::class])]
final class OrderClassification
{
    public readonly PremiumOrder|StandardOrder|BulkOrder $being;
    
    public function __construct(
        #[Input] Money $amount,
        #[Input] int $quantity,
        #[Input] bool $isPremiumCustomer,
        #[Inject] PricingService $pricing
    ) {
        $this->being = match (true) {
            $isPremiumCustomer && $amount->greaterThan(Money::USD(1000)) 
                => new PremiumOrder($amount, $quantity, $pricing->premiumDiscount()),
            $quantity > 50 
                => new BulkOrder($amount, $quantity, $pricing->bulkDiscount()),
            default 
                => new StandardOrder($amount, $quantity)
        };
    }
}
```

### Validation-Based Becoming

```php
#[Be([ValidatedInput::class, InvalidInput::class])]
final class InputValidation
{
    public readonly ValidatedInput|InvalidInput $being;
    
    public function __construct(
        #[Input] string $email,
        #[Input] string $password,
        #[Inject] ValidationService $validator
    ) {
        $errors = $validator->validate($email, $password);
        
        $this->being = empty($errors)
            ? new ValidatedInput($email, $password)
            : new InvalidInput($errors);
    }
}
```

## Metamorphosis Continuation

Type-driven objects can continue their metamorphosis journey:

```php
// First transformation: Input → Classification
$classification = $becoming(new OrderInput($data));

// Second transformation: Classification continues based on its being property
$processedOrder = $becoming($classification);  // Uses the being property automatically
```

The framework **automatically extracts** the being property for continued metamorphosis.

## Error States as Valid Beings

Failure is not an exception—it's a **valid form of existence**:

```php
#[Be([SuccessfulRegistration::class, FailedRegistration::class])]
final class UserRegistration
{
    public readonly SuccessfulRegistration|FailedRegistration $being;
    
    public function __construct(
        #[Input] string $email,
        #[Input] string $password,
        #[Inject] UserService $userService
    ) {
        try {
            $user = $userService->register($email, $password);
            $this->being = new SuccessfulRegistration($user);
        } catch (RegistrationException $e) {
            $this->being = new FailedRegistration($e->getErrors());
        }
    }
}
```

Both success and failure are **equally valid beings**.

## Philosophical Implications

### Objects as Conscious Entities

Type-Driven Metamorphosis treats objects as **conscious beings** that understand their own nature and choose their destiny accordingly.

### Wu Wei in Code

The programmer doesn't **force** the transformation—they create conditions where objects naturally become what they are meant to be.

### Elimination of Control Flow

No `if-else` chains in business logic. The object's nature **is** the logic.

## Practical Benefits

### 1. **Self-Documenting Decisions**
The Being Property signature shows all possible outcomes:
```php
public readonly Success|Warning|Error $being;  // Clear possibilities
```

### 2. **Impossible States Prevention**
Union types ensure only valid combinations exist.

### 3. **Natural Error Handling**
Errors become valid beings, not exceptions to avoid.

### 4. **Composable Transformations**
Each stage can continue the metamorphosis journey naturally.

## Best Practices

### Being Property Design
- Use **descriptive union types** that represent domain concepts
- Order types from **most likely to least likely** for readability
- Keep the union **focused**—typically 2-4 types maximum

### Constructor Logic
- Use **match expressions** for clear branching logic
- Let the object's **nature determine** its destiny
- Avoid complex conditional nesting

### Destination Classes
- Each destination should be a **complete being** in its own right
- Design for **continued metamorphosis** if needed
- Maintain **immutability** throughout the chain

---

**Next**: Learn about [Reason Layer: Ontological Capabilities](07-reason-layer.md) where contextual capabilities shape transformation.

*"In Type-Driven Metamorphosis, we don't decide what objects become—we discover what they already are, in their deepest nature."*