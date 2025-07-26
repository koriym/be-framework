# Contributing to Be Framework

> "When we transform, it's a moment of universal evolution" — From the Being Paradigm

Welcome to the Be Framework community. This project represents more than code—it's a philosophical exploration of what programming can become when aligned with the deepest principles of existence and transformation.

## Philosophy of Contribution

Be Framework embodies the principle of **Wu Wei (無為)**—natural action without forcing. In this spirit, contributions emerge organically from genuine understanding and authentic engagement with the paradigm.

### What We Seek

**Philosophical Insights**
- Deep reflections on the intersection of programming and philosophy
- Connections between ancient wisdom and modern code
- Explorations of ontological questions in software design

**Practical Implementations**
- Examples demonstrating ontological programming principles
- Patterns that embody "Be, Don't Do" philosophy
- Code that serves as both functionality and philosophical expression

**Educational Content**
- Documentation that bridges theory and practice
- Tutorials that guide developers through paradigm shifts
- Translations that make these concepts accessible globally

**Thoughtful Questions**
- Challenges that deepen our understanding
- Edge cases that test our philosophical foundations
- Critiques that help refine the paradigm

## Ways to Contribute

### 1. Engage in Dialogue

The best contributions often begin with conversation:
- Open issues for philosophical questions
- Share your understanding of the concepts
- Discuss how the paradigm applies to your domain

### 2. Share Your Metamorphosis

Document your journey of understanding:
- How did the paradigm change your thinking?
- What insights emerged from your experience?
- Where did you struggle, and how did you overcome it?

### 3. Create Examples

Show the paradigm in action:
- Real-world applications of ontological programming
- Comparisons with traditional approaches
- Demonstrations of philosophical principles in code

### 4. Expand Documentation

Help others on their journey:
- Clarify complex concepts
- Add missing explanations
- Improve existing documentation

## Development Guidelines

### Before Contributing Code

1. **Understand the Philosophy**: Read the core papers in `docs/papers/philosophy/`
2. **Explore the Manual**: Work through `docs/manual/` step by step
3. **Engage with Examples**: Study `examples/` to see patterns in practice

### Code Style

Follow the ontological programming principles:

**Constructor-Only Logic**
```php
// Good: Transformation in constructor
final class ValidatedUser
{
    public function __construct(
        #[Input] UserInput $input,        // Immanent
        #[Inject] Validator $validator    // Transcendent
    ) {
        // All logic here
    }
}

// Avoid: Methods that transform state
final class User
{
    public function validate() { /* ... */ }
}
```

**Immutable Properties**
```php
// Good: Public readonly properties
final class Order
{
    public readonly string $id;
    public readonly DateTime $createdAt;
}

// Avoid: Mutable state
final class Order
{
    private string $status;
    public function setStatus(string $status) { /* ... */ }
}
```

**Semantic Naming**
```php
// Good: Names that reflect being
final class AuthenticatedUser { }
final class ValidatedEmail { }
final class ProcessedOrder { }

// Avoid: Action-oriented names
final class UserProcessor { }
final class EmailValidator { }
```

### Testing Philosophy

Tests in Be Framework serve as **examples** rather than mere verification:

```php
// Good: Test as example of natural flow
public function testUserRegistrationFlow(): void
{
    $input = new UserInput('user@example.com');
    $validated = new ValidatedUser($input, $this->validator);
    $registered = new RegisteredUser($validated, $this->repository);
    
    $this->assertInstanceOf(RegisteredUser::class, $registered);
}
```

## Submitting Contributions

### Pull Request Process

1. **Fork and Branch**: Create a feature branch from `master`
2. **Develop with Intent**: Ensure your code embodies the paradigm
3. **Document the Why**: Explain the philosophical reasoning behind changes
4. **Test as Examples**: Write tests that demonstrate rather than just verify
5. **Open Pull Request**: Include context about your contribution's significance

### Commit Messages

Write commit messages that reflect the transformation:

```
Add ValidatedEmail class for email verification metamorphosis

This introduces a new being class that transforms raw email input
into validated email state through constructor injection, following
the principle of existence = correctness.
```

## Community Guidelines

### Be, Don't Do

Approach discussions with the spirit of Wu Wei:
- **Listen deeply** before responding
- **Transform through understanding** rather than forcing agreement
- **Accept different perspectives** as part of the natural flow
- **Let ideas evolve organically** rather than pushing agendas

### Embrace Mono no Aware (物の哀れ)

Recognize the beauty in transient understanding:
- It's natural for concepts to evolve
- Previous approaches had their time and value
- New insights emerge from the dissolution of old ones

### Practice Ma (間)

Leave space for others to contribute:
- Pause between responses to allow reflection
- Value silence and contemplation
- Don't fill every conceptual space immediately

## Recognition

Contributors to Be Framework become part of an evolving philosophical tradition. Your contributions—whether code, documentation, insights, or questions—help shape the future of programming as a contemplative practice.

## Getting Help

- **Philosophical Questions**: Open an issue with the `philosophy` label
- **Technical Help**: Open an issue with the `technical` label  
- **Documentation**: Open an issue with the `documentation` label

Remember: In the spirit of Wu Wei, the best questions often arise naturally from genuine engagement with the concepts.

---

*"We didn't refute the criticism—we metabolized it"* — From the project dialogue

Your contributions help the paradigm grow through the same process of thoughtful transformation that defines Be Framework itself.