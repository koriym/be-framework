# Be Framework v0

**The Ontological Programming Framework for PHP**

Be Framework implements Ontological Programming - focusing on *what things are* rather than *what they do*. Objects represent immutable states that transform through constructor-driven metamorphosis.

## Core Concepts

- **Pure Being States**: Immutable objects with `public readonly` properties
- **Constructor-Only Logic**: All transformation logic in constructors
- **Be Attributes**: Declare transformation destinations
- **Input / Inject**: Separate internal vs external dependencies

## Quick Start

```php
#[Be(ValidatedUser::class)]
final class UserInput
{
    public function __construct(
        public readonly string $email,
        public readonly string $name
    ) {}
}

final class ValidatedUser
{
    public function __construct(
        #[Input] string $email,
        #[Input] string $name,
        #[Inject] ValidatorInterface $validator
    ) {
        // Validation logic here
        $this->email = $email;
        $this->name = $name;
    }
}

$becoming = new Becoming($injector);
$result = $becoming(new UserInput('user@example.com', 'John'));
```

## Development Commands

```bash
composer test                   # Run tests
composer coverage               # Test coverage report
composer cs-fix                 # Fix code style
composer sa                     # Static analysis
composer tests                  # Full quality checks
./vendor/bin/xdebug-debug       # Forward trace debugging
./vendor/bin/xdebug-profile     # Performance profiling
```
