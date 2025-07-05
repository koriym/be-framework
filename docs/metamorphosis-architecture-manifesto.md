# The Metamorphosis Architecture: A Design Manifesto

## Foreword: A Dialogue on Existence

This document is the crystallized result of a profound dialogue between a human and an AI. It began with a simple question—"What if we programmed by defining what can exist, rather than what should happen?"—and evolved into a comprehensive architectural philosophy. Every principle herein was forged through challenge, refinement, and shared discovery. This is not just a specification; it is the chronicle of a thought process.

**Core Philosophy:** Ontological Programming  
**Architectural Pattern:** The Metamorphosis Architecture

---

## 1. Core Principles: The Laws of Existence

This architecture is founded on a set of uncompromising principles derived from Ontological Programming.

### Principle 1: Existence is Defined in the Constructor
All logic required for an object to validly exist resides exclusively within its constructor. If the constructor completes without error, the object exists, and its existence is proof of its validity.

### Principle 2: Being is Immutable
Once an object exists, its state cannot be changed. It is a perfect, immutable fact. All properties are `public readonly`. Transformation is not mutation, but the creation of a new existence.

### Principle 3: A Class is a Single, Perfect Stage of Being
Each class, which we shall call a **Metamorphosis Class**, represents one distinct, complete, and self-contained stage of a process. It knows only of its own existence, not of what came before or what comes after.

### Principle 4: Control Flow is Metamorphosis
The progression of a process is not a series of method calls, but a chain of metamorphoses, where one existence (a Metamorphosis Class) gives rise to the next.

---

## 2. The Anatomy of a Metamorphosis Class

A Metamorphosis Class is the fundamental building block of the architecture.

```php
/**
 * A Metamorphosis Class represents a single, validated state of being.
 * Its name should describe what it *is*, not what it *does*.
 *
 * Example: A user registration that has been validated but not yet saved.
 */
final class ValidatedRegistration
{
    // Immutable, public properties define the "essence" of this existence.
    public readonly string $email;
    public readonly string $password;

    /**
     * The constructor is the gate of existence.
     * It receives its "past life" (previous stage) and the "tools"
     * (dependencies) needed to bring itself into being.
     */
    public function __construct(
        #[Input] RegistrationInput $rawInput,
        UserValidator $validator
    ) {
        // The existence condition: validation must pass.
        // If it fails, an exception is thrown, and this object never exists.
        $validator->validate($rawInput->email, $rawInput->password);

        // If validation passes, its essence is established.
        $this->email = $rawInput->email;
        $this->password = $rawInput->password;
    }
}
```

---

## 3. The Core Challenge: Branching the Flow of Existence

A linear pipeline is simple, but reality requires conditional paths. The central design question is: how does a Metamorphosis Class, which knows nothing of the future, decide which path to take?

The solution is a specialized Metamorphosis Class acting as a **"Traffic Controller."** Its sole responsibility is to make a decision and kick off the appropriate downstream pipeline. It does not hold state; it only directs flow.

```php
/**
 * A Traffic Controller. Its purpose is to decide which metamorphosis
 * happens next, based on a condition.
 */
final class RegistrationRouter
{
    /**
     * The constructor receives the validated input and the "factories"
     * for each potential path.
     */
    public function __construct(
        #[Input] ValidatedRegistration $validated,
        UserRepository $userRepo,
        UnverifiedUserFactory $unverifiedUserFactory,
        UserConflictFactory $userConflictFactory
    ) {
        // --- The Guard Clause: Handle exceptional paths first. ---
        // This makes the primary "happy path" clearer.
        if ($userRepo->existsByEmail($validated->email)) {
            // Kick off the "user conflict" pipeline and stop.
            $userConflictFactory->create($validated->email);
            return;
        }

        // --- The Happy Path: The main flow of logic. ---
        // This code is only reached if the guard clause condition was false.
        $unverifiedUserFactory->create($validated->email, $validated->password);
    }
}
```

This pattern elegantly solves branching without violating the core principles. The `RegistrationRouter` itself is stateless; its existence is fleeting, its only purpose to trigger the next, more permanent existence.

---

## 4. The Principle of Verifiable Behavior

How do we test a class that returns nothing and has no state? We test its **behavior**. We verify that it interacted with its dependencies as expected. This is achieved by injecting a **"Spy"** during tests.

A Spy is a test-specific implementation of a dependency (like a factory) that records how it was used.

```php
// A Spy Factory for testing purposes.
class SpyUserConflictFactory implements UserConflictFactory
{
    public int $callCount = 0;
    public ?string $capturedEmail = null;

    public function create(string $email): UserConflict 
    {
        $this->callCount++;
        $this->capturedEmail = $email;
        return new UserConflict($email); // Return a dummy or real instance
    }
}

// In the test for RegistrationRouter:
$spyFactory = new SpyUserConflictFactory();
// ... inject the spy ...
new RegistrationRouter(/* ... */, $spyFactory);

// Assert the BEHAVIOR, not the state.
$this->assertEquals(1, $spyFactory->callCount);
$this->assertEquals('test@example.com', $spyFactory->capturedEmail);
```

This ensures that our tests are decoupled from implementation details and focused on the observable contract of a class.

---

## 5. The Sanctity of Type Safety

A crucial design decision is whether the factories used for branching should be generic (e.g., `create(string $className, ...$args)`) or dedicated and type-safe (e.g., `create(string $email): UserConflict`).

This architecture resolutely chooses **type safety**.

A generic factory abandons the compiler and static analysis tools, reintroducing an entire class of runtime errors (typos in strings, incorrect argument types).

Defining dedicated, type-safe factory interfaces, while seemingly verbose, is an act of immense value:

- **It is a Contract.** It guarantees the data flowing between stages is correct by definition.
- **It is a Statement of Intent.** `UserConflictFactory` unambiguously declares its purpose.
- **It is a Metric of Complexity.** The number of factory interfaces in a project provides a structural map of its major decision points. They are not boilerplate; they are living documentation.

---

## 6. Implementation Example: User Registration Flow

The following code demonstrates the complete implementation of a user registration flow, showcasing all the principles of the Metamorphosis Architecture.

```php
/**
 * ------------------------------------------------------------------
 * 1. FACTORY INTERFACES (The Type-Safe Contracts)
 * ------------------------------------------------------------------
 */

interface UnverifiedUserFactory
{
    public function create(string $email, string $password): UnverifiedUser;
}

interface UserConflictFactory
{
    public function create(string $email): UserConflict;
}

/**
 * ------------------------------------------------------------------
 * 2. THE PIPELINE STAGES (Metamorphosis Classes)
 * ------------------------------------------------------------------
 */

// STAGE 1: The Raw Input (The Egg)
#[To(ValidatedRegistration::class)]
final class RegistrationInput
{
    public function __construct(
        #[Input] public readonly string $email,
        #[Input] public readonly string $password,
        #[Input] public readonly string $passwordConfirmation
    ) {}
}

// STAGE 2: The Validated Input (The Larva)
#[To(RegistrationRouter::class)]
final class ValidatedRegistration
{
    public function __construct(
        #[Input] public readonly string $email,
        #[Input] public readonly string $password,
        #[Input] string $passwordConfirmation,
        UserValidator $validator
    ) {
        // Existence Conditions:
        $validator->validateEmailFormat($this->email);
        $validator->validatePasswordStrength($this->password);
        $validator->validatePasswordsMatch($this->password, $passwordConfirmation);
    }
}

// STAGE 3: The Router (Traffic Controller)
final class RegistrationRouter
{
    public function __construct(
        #[Input] ValidatedRegistration $validated,
        UserRepository $userRepo,
        UnverifiedUserFactory $unverifiedUserFactory,
        UserConflictFactory $userConflictFactory
    ) {
        if ($userRepo->existsByEmail($validated->email)) {
            $userConflictFactory->create($validated->email);
            return;
        }
        
        $unverifiedUserFactory->create($validated->email, $validated->password);
    }
}

// HAPPY PATH - STAGE 4: The Unverified User
#[To(VerificationEmailSent::class)]
final class UnverifiedUser
{
    public readonly string $userId;
    public readonly string $verificationToken;

    public function __construct(
        string $email,
        string $password,
        PasswordHasher $hasher,
        TokenGenerator $tokenGenerator,
        UserRepository $userRepo
    ) {
        $user = $userRepo->createUnverified(
            $email, 
            $hasher->hash($password), 
            $tokenGenerator->generate()
        );
        $this->userId = $user->id;
        $this->verificationToken = $user->verificationToken;
    }
}

// HAPPY PATH - STAGE 5: The Notification Sent
#[To(JsonResponse::class, statusCode: 201)]
final class VerificationEmailSent
{
    public readonly string $message = 'Registration successful. Please check your email to verify your account.';
    public readonly string $userId;

    public function __construct(
        #[Input] string $userId,
        #[Input] string $verificationToken,
        UserEmailResolver $emailResolver,
        MailerInterface $mailer
    ) {
        $email = $emailResolver->getEmailForUser($userId);
        $mailer->sendVerificationEmail($email, $verificationToken);
        $this->userId = $userId;
    }
}

// CONFLICT PATH - STAGE 4: The User Conflict
#[To(JsonResponse::class, statusCode: 409)]
final class UserConflict
{
    public readonly string $error = 'User already exists';
    public readonly string $message;

    public function __construct(string $email)
    {
        $this->message = "The email address '{$email}' is already registered.";
    }
}
```

---

## 7. Final Distillation: The Metamorphosis Manifesto

This journey of inquiry leads us to a final set of principles, a manifesto for this way of building software.

1. **We Define Being, Not Doing.** Our classes describe what can exist, not what should happen.

2. **The Constructor is the Sole Arbiter of Existence.** All validity is proven here, or not at all.

3. **Immutability is Non-Negotiable.** An existence, once established, is an immutable fact.

4. **Behavior is Transformed into Structure.** Conditional logic becomes a "Traffic Controller" class. Dependencies become type-safe "Factory" contracts.

5. **We Test Interactions, Not State.** We verify that our objects behave correctly by observing their interactions with the world.

---

## Conclusion

This is the Metamorphosis Architecture. It is a path toward building systems that are not merely functional, but are structurally sound, demonstrably correct, and philosophically coherent.

The architecture transforms runtime complexity into design-time clarity. By making impossible states unrepresentable and branching logic explicit through type-safe factories, we create systems where correctness is not hoped for but guaranteed by construction.

May this manifesto guide you toward building more reliable, understandable, and maintainable software.