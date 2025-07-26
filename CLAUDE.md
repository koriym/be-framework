# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Be Framework is a PHP framework implementing the Ontological Programming paradigm, where data transformation occurs through pure constructor-driven metamorphosis. The framework treats all data transformations as "becoming" - continuous metamorphosis through constructor injection.

## Development Commands

All development commands are run from the `poc/php/` directory:

```bash
cd poc/php/

# Testing
composer test                   # Run unit tests
composer coverage               # Generate test coverage report
composer phpdbg                 # Generate coverage with phpdbg
composer pcov                   # Generate coverage with pcov

# Code Quality
composer cs                     # Check coding style with phpcs
composer cs-fix                 # Fix coding style with phpcbf
composer phpstan                # Static analysis with PHPStan
composer psalm                  # Type checking with Psalm
composer phpmd                  # Code analysis with PHPMD
composer sa                     # Run both phpstan and psalm

# Comprehensive Quality Checks
composer tests                  # Run cs + sa + test
composer build                  # Full build: clean + cs + sa + coverage + crc + metrics

# Utilities
composer clean                  # Clear analysis caches
composer baseline               # Generate baselines for PHPStan and Psalm
composer crc                    # Run composer require checker
composer metrics                # Generate code metrics report
```

## Core Architecture

### Central Concepts

**Being Classes**: Every class represents a stage of existence/transformation with these characteristics:
- All properties are `public readonly` (immutable state)
- All transformation logic happens in constructors only
- Use `#[Be(NextClass::class)]` attribute to declare transformation destiny
- Support branching with `#[Be([SuccessClass::class, FailureClass::class])]`

**Metamorphosis Engine**: The `Becoming` class executes transformations:
- Takes an input object and processes it through continuous transformations
- Follows `#[Be]` attributes to determine next transformation stage
- Continues until no further transformation is possible

**Dependency Declaration**: All constructor parameters must be explicitly attributed:
- `#[Input]` - Values from the previous object's properties (Immanent factors)
- `#[Inject]` - Dependencies from DI container (Transcendent factors)
- Both attributes are mutually exclusive and required for all parameters

### Key Files

- `poc/php/src/Becoming.php` - The metamorphosis engine that executes transformations
- `poc/php/src/Be.php` - Attribute for declaring transformation destinations
- `poc/php/src/BecomingArguments.php` - Resolves constructor arguments during transformation
- `poc/php/src/GetClass.php` - Utility to extract next transformation class from #[Be] attributes

### Naming Conventions

Follow ontological naming patterns from `docs/manual/convention/naming-standards.md`:

**Input Classes**: `{Domain}Input` (e.g., `UserInput`, `OrderInput`)
**Being Classes**: `Being{Domain}` or `{Domain}Being` (e.g., `BeingUser`, `BeingOrder`)  
**Final Objects**: Domain-specific result names (e.g., `ValidatedUser`, `ProcessedOrder`, `Success`, `Failure`)

**Properties**:
- Use `$being` for union type properties carrying transformation results
- Name properties to reflect what the object *is*, not what it *does*
- Always comment constructor parameters as `// Immanent` or `// Transcendent`

### Directory Structure

```
poc/                           # Proof of concept (complete)
├── php/                      # PHP implementation
│   ├── src/                 # Framework source code
│   │   ├── Be.php          # #[Be] attribute for transformation destiny
│   │   ├── Becoming.php    # Core metamorphosis engine
│   │   ├── BecomingArguments.php # Constructor argument resolution
│   │   ├── Debug/          # Debug utilities
│   │   └── Exception/      # Framework exceptions
│   ├── tests/              # Unit tests
│   │   ├── BecomingTest.php # Core framework tests
│   │   └── Fake/           # Test fixtures and mocks
│   └── composer.json       # Dependencies and scripts
├── ts/                      # TypeScript implementation (AI-generated)
│   ├── src/                # Framework source code
│   ├── tests/              # Unit tests
│   └── package.json        # Dependencies and scripts
└── README.md               # POC completion summary

docs/                        # Comprehensive documentation
├── papers/                  # Academic papers and deep theory
│   ├── philosophy/         # Ontological programming concepts
│   ├── framework/          # Technical specifications
│   └── patterns/           # Implementation patterns
├── manual/                  # Tutorial-style manual
│   ├── convention/         # Naming and coding standards
│   └── (tutorial chapters)
├── study/                   # Learning and exploration
│   ├── podcast/            # Audio learning content
│   └── (AI dialogue content)
└── faq/                    # Frequently asked questions

examples/                    # Working examples
├── basic-demo.php          # Simple transformation example
└── user-registration/      # Complete user registration implementation
```

## Framework Philosophy

**"Be, Don't Do"** - Focus on what objects *are* rather than what they *do*:
- Objects undergo metamorphosis through constructor injection
- All state is immutable (`public readonly`)
- Transformations are declarative via `#[Be]` attributes
- Clear separation between Immanent (internal) and Transcendent (external) factors

**Constructor-Only Logic**: All business logic and validation occurs in constructors. No methods for data transformation.

**Type Transparency**: No hidden state or mystery boxes. Everything is explicit and statically analyzable.

## Documentation

The extensive documentation is organized in `docs/` with key starting points:
- `docs/README.md` - Complete documentation guide
- `docs/manual/index.md` - Tutorial-level framework manual
- `docs/papers/framework/be-framework-whitepaper.md` - Technical overview
- `docs/papers/philosophy/ontological-programming-paper.md` - Philosophical foundations

## Testing

Tests are located in `poc/php/tests/` and follow the existing patterns:
- Use fake objects in `tests/Fake/` directory for mocking
- Test metamorphosis chains and transformations
- Verify proper attribute handling and dependency injection
- Run tests with `composer test` from the `poc/php/` directory

TypeScript tests are in `poc/ts/tests/` with Jest framework.

## Common Patterns

**Basic Transformation**:
```php
#[Be(ProcessedData::class)]
final class DataInput
{
    public function __construct(
        public readonly string $data  // Immanent
    ) {}
}

final class ProcessedData  
{
    public function __construct(
        #[Input] string $data,              // Immanent  
        #[Inject] DataProcessor $processor  // Transcendent
    ) {
        $this->result = $processor->process($data);
    }
}
```

**Branching Transformation**:
```php
#[Be([ValidUser::class, InvalidUser::class])]
final class BeingUser
{
    public function __construct(
        public readonly string $email,           // Immanent
        public readonly ValidUser|InvalidUser $being  // Result union type
    ) {}
}
```

**Execution**:
```php
$becoming = new Becoming($injector);
$result = $becoming(new DataInput('sample'));
```
