# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Be Framework v0 - The Ontological Programming Framework for PHP implementing "Be, Don't Do" philosophy. Objects represent immutable states that undergo constructor-driven metamorphosis through `#[Be]` attributes.

**Documentation**: https://be-framework.github.io/

## Core Architecture

### Metamorphosis Engine
The `Becoming` class (`src/Becoming.php`) processes objects through continuous transformations:
- Follows `#[Be]` attributes to determine transformation paths
- Linear: `#[Be(NextClass::class)]` or Branching: `#[Be([ClassA::class, ClassB::class])]`
- All business logic in constructors - no methods for data transformation
- Properties must be `public readonly` for immutability

### Constructor Argument Resolution
- **`#[Input]`**: Immanent data from source object properties
- **`#[Inject]`**: Transcendent dependencies from DI container (Ray.Di)
- Arguments resolved by `BecomingArguments` class during metamorphosis

### Key Components
- **`src/Attribute/`**: Framework attributes (`Be`, `Validate`, `Message`, `SemanticTag`)
- **`src/BecomingArguments.php`**: Constructor argument resolution
- **`src/BecomingType.php`**: Type matching for branching transformations
- **`src/SemanticLog/`**: Transformation lifecycle logging
- **`src/SemanticVariable/`**: Semantic validation for constructor parameters

## Development Commands

```bash
# Testing
composer test                    # Run all unit tests
php vendor/bin/phpunit --filter testMethodName   # Run specific test method
php vendor/bin/phpunit tests/TestFile.php        # Run specific test file

# Code Quality (ALWAYS run after modifying PHP files)
composer cs-fix                  # Auto-fix coding style issues (Doctrine standards)
composer cs                      # Check coding style without fixing
composer sa                      # Run static analysis (phpstan + psalm)
composer phpmd                   # Analyze PHP code for potential issues

# Coverage
composer coverage                # Generate coverage report with xdebug
composer phpdbg                  # Generate coverage with phpdbg
composer pcov                    # Generate coverage with pcov

# Comprehensive Checks
composer tests                   # Run cs + sa + phpmd + test
composer build                   # Full build: clean + cs + sa + phpmd + coverage + crc

# Utilities
composer clean                   # Clear analysis caches
composer baseline                # Generate baselines for PHPStan and Psalm
composer crc                     # Run composer require checker
composer metrics                 # Generate code metrics report
```

## Debugging Tools

### Forward Trace Debugging (xdebug-debug)
```bash
# Always verify test works first
php vendor/bin/phpunit --filter testMethodName tests/TestFile.php

# Then add tracing
./vendor/bin/xdebug-debug --context="Debug context" \
  --break="file.php:lineNumber" \
  --exit-on-break \
  --steps=10 \
  --json \
  -- php vendor/bin/phpunit --filter testMethodName tests/TestFile.php
```

### Performance Profiling
```bash
./vendor/bin/xdebug-profile -- php vendor/bin/phpunit --filter testMethodName
```

## Testing

```bash
# Run specific test method (use --filter, not ::method)
php vendor/bin/phpunit --filter testMethodName

# Run specific test file
php vendor/bin/phpunit tests/SemanticLog/LoggerTest.php

# Run tests matching pattern
php vendor/bin/phpunit --filter "Semantic"
```

- Test fixtures in `tests/Fake/` and `tests/FakeApp/`
- Schema validation tests verify semantic log output against `docs/schemas/*.json`

## Creating Transformation Classes

1. Declare transformation with `#[Be]` attribute
2. Use `public readonly` properties only
3. Put all logic in constructor
4. Use `#[Input]` for data from source object
5. Use `#[Inject]` for external dependencies

```php
#[Be(ProcessedData::class)]
final class InputData
{
    public function __construct(
        public readonly string $value
    ) {}
}

final class ProcessedData
{
    public readonly string $result;

    public function __construct(
        #[Input] string $value,           // From InputData
        #[Inject] ProcessorService $proc  // From DI container
    ) {
        $this->result = $proc->process($value);
    }
}
```

## Code Style Guidelines

### Early Return Pattern (No Else)
Avoid `else` statements - use early returns:

```php
// ✅ Good
if ($condition) {
    return $this->handleCondition();
}
return $this->handleDefault();

// ❌ Avoid
if ($condition) {
    return $this->handleCondition();
} else {
    return $this->handleDefault();
}
```

### After Modifying PHP Files
**ALWAYS** run: `composer cs-fix`