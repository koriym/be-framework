# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Be Framework v0 - Production implementation of Ontological Programming paradigm where data transformations occur through constructor-driven metamorphosis with comprehensive semantic logging.

## Core Architecture

### Metamorphosis Engine
The `Becoming` class (`src/Becoming.php`) is the central engine that processes objects through continuous transformations:
- Takes an input object and follows `#[Be]` attributes to determine transformation paths
- Supports linear transformations (`#[Be(NextClass::class)]`) and branching (`#[Be([ClassA::class, ClassB::class])]`)
- All transformation logic resides in constructors - no methods for data transformation
- Properties are `public readonly` ensuring immutability

### Key Components
- **`src/Attribute/Be.php`**: Declares transformation destinations for objects
- **`src/BecomingArguments.php`**: Resolves constructor arguments during metamorphosis
- **`src/Being.php`**: Utility to extract next transformation class from attributes
- **`src/BecomingType.php`**: Type matching and branching logic for transformations
- **`src/SemanticLog/`**: Comprehensive logging infrastructure capturing transformation lifecycle
- **`src/SemanticVariable/`**: Semantic validation system for constructor parameters

### Semantic Logging System
The framework includes sophisticated semantic logging that tracks:
- **Open Context**: Logs when a transformation begins (immanent/transcendent sources)
- **Close Context**: Logs transformation completion with resulting properties
- **Destinations**: SingleDestination, MultipleDestination, FinalDestination, or DestinationNotFound
- Schema validation via `docs/schemas/` JSON schemas

## Development Commands

```bash
# Testing
composer test                    # Run all unit tests
php vendor/bin/phpunit --filter testMethodName   # Run specific test method
php vendor/bin/phpunit path/to/TestFile.php      # Run specific test file

# Code Quality
composer cs                      # Check coding style (phpcs)
composer cs-fix                  # Auto-fix coding style issues
composer sa                      # Run static analysis (phpstan + psalm)
composer phpstan                 # Run PHPStan only
composer psalm                   # Run Psalm only
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

## Forward Trace Debugging

### Core Workflow
1. **Always verify the command works first**:
   ```bash
   php vendor/bin/phpunit --filter testMethodName tests/TestFile.php
   ```

2. **Then add xdebug-debug for tracing**:
   ```bash
   ./vendor/bin/xdebug-debug --context="Debug context" \
     --break="file.php:lineNumber" \
     --exit-on-break \
     --steps=10 \
     --json \
     -- php vendor/bin/phpunit --filter testMethodName tests/TestFile.php
   ```

### Best Practices
- Use `--steps=10-20` for optimal signal-to-noise ratio
- Target specific lines with breakpoints
- Focus on `"recording_type": "diff"` entries in output
- Use PHPUnit's `--filter` flag (not `::method` syntax)

## Testing Patterns

### Running Tests
```bash
# Run all tests
composer test

# Run specific test class
php vendor/bin/phpunit tests/SemanticLog/LoggerTest.php

# Run specific test method
php vendor/bin/phpunit --filter testMultipleDestination

# Run tests matching pattern
php vendor/bin/phpunit --filter "Semantic"
```

### Test Organization
- Unit tests in `tests/` mirror `src/` structure
- Test fixtures in `tests/Fake/` for mock objects
- Each test class tests a single production class
- Schema compliance tests validate semantic log output

## Code Style Requirements

**CRITICAL**: After modifying PHP files, always run:
```bash
composer cs-fix
```

This ensures consistent code formatting following Doctrine Coding Standards.

## Key Directories

```
src/
├── Attribute/           # Framework attributes (#[Be], #[Validate], #[Message], #[SemanticTag])
├── SemanticLog/        # Semantic logging infrastructure
│   └── Context/        # Log context objects (destinations, metamorphosis)
├── SemanticVariable/   # Semantic validation system
├── Exception/          # Framework exceptions
├── Becoming.php        # Core metamorphosis engine
├── BecomingArguments.php # Constructor argument resolution
├── Being.php           # Attribute extraction utility
├── BecomingType.php    # Type matching and branching logic
└── Types.php           # Type utilities

tests/
├── Fake/               # Test fixtures and mock objects
├── FakeApp/            # Application test examples
├── SemanticLog/        # Semantic logging tests
└── SemanticVariable/   # Semantic validation tests

docs/
└── schemas/            # JSON schemas for log validation
```

## Common Development Tasks

### Adding a New Transformation Class
1. Create class with `#[Be]` attribute declaring next transformation
2. Use `public readonly` properties for immutable state
3. Put all logic in constructor
4. Add corresponding test in `tests/`

### Debugging Failed Tests
1. Run the specific failing test:
   ```bash
   php vendor/bin/phpunit --filter testMethodName
   ```
2. Use xdebug-debug for detailed trace if needed
3. Check semantic log output matches expected schema

### Fixing Code Style Issues
```bash
composer cs-fix     # Auto-fix issues
composer cs         # Check without fixing
```

## Important Notes

- The framework follows "Be, Don't Do" philosophy - objects represent states, not behaviors
- All business logic happens in constructors during metamorphosis
- Properties must be `public readonly` for immutability
- Transformations are irreversible and declarative via `#[Be]` attributes
- Semantic logging captures complete transformation lifecycle for observability

## Code Style Guidelines

### Control Flow - Early Return Pattern
**IMPORTANT**: Avoid `else` statements - use early returns for cleaner, more readable code.

**❌ Avoid:**
```php
public function process(string $input): string
{
    if ($condition) {
        return $this->handleCondition($input);
    } else {
        return $this->handleDefault($input);
    }
}
```

**✅ Prefer:**
```php
public function process(string $input): string
{
    if ($condition) {
        return $this->handleCondition($input);
    }
    
    return $this->handleDefault($input);
}
```

**Multiple conditions:**
```php
public function validate(array $data): bool
{
    if (empty($data)) {
        return false;
    }
    
    if (! $this->hasRequiredFields($data)) {
        return false;
    }
    
    return $this->performValidation($data);
}
```

**Benefits of Early Returns:**
- Reduces nesting and cognitive load
- Makes error conditions explicit
- Eliminates else-related branching complexity
- Improves readability and maintainability
- Follows "fail fast" principle