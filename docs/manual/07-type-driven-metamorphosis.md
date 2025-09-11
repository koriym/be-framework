# 6. Type-Driven Metamorphosis

> "The object knows its own nature. We merely create the conditions for its becoming."

Type-Driven Metamorphosis represents the deepest transformation—where objects **discover their own destiny** through their nature.

## Beyond Fixed Paths

Traditional metamorphosis follows predetermined routes:

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
#[Be([ActiveUser::class, InactiveUser::class])]
final class UserValidation
{
    public readonly ActiveUser|InactiveUser $being;  // Being Property
    
    public function __construct(
        #[Input] string $email,
        #[Input] DateTime $lastLogin,
        #[Inject] UserRepository $repository
    ) {
        $daysSinceLogin = $lastLogin->diff(new DateTime())->days;
        
        // Self-determination of destiny
        $this->being = $daysSinceLogin < 30 
            ? new ActiveUser($email, $lastLogin)
            : new InactiveUser($email, $lastLogin);
    }
}
```

## The Being Property

The **Being Property** is where self-determination manifests:

- Must be a **union type** of all possible destinations
- Must be named exactly `being`
- Contains the object's chosen destiny

```php
public readonly SuccessfulPayment|FailedPayment $being;
```

## Natural Branching

Objects choose their path based on **inner nature**:

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
        $this->being = match (true) {
            $age < 18 => $factory->createChild($name, $age),
            $age < 65 => $factory->createAdult($name, $age),
            default => $factory->createSenior($name, $age)
        };
    }
}
```

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

## Metamorphosis Continuation

The framework automatically extracts the Being Property for continued transformation:

```php
$classification = $becoming(new OrderInput($data));
$processedOrder = $becoming($classification);  // Uses being property automatically
```

## Philosophical Implications

### Objects as Conscious Entities

Type-Driven Metamorphosis treats objects as **conscious beings** that understand their own nature.

### Wu Wei in Code

The programmer doesn't **force** transformation—they create conditions where objects naturally become what they are meant to be.

### Elimination of Control Flow

No `if-else` chains in business logic. The object's nature **is** the logic.

## The Revolution

Being Property signatures become **documentation**:
```php
public readonly Success|Warning|Error $being;  // All possibilities visible
```

Objects **self-determine** their destiny based on their essential nature, not external control.

---

**Next**: Learn about [Reason Layer: Ontological Capabilities](07-reason-layer.md) where contextual capabilities shape transformation.

*"We don't decide what objects become—we discover what they already are, in their deepest nature."*