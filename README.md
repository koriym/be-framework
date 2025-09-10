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
#[Be(ProcessedOrder::class)]
final class OrderInput
{
    public function __construct(
        public readonly string $productId,
        public readonly int $quantity
    ) {}
}

final class ProcessedOrder
{
    public readonly float $total;
    public readonly string $status;
    
    public function __construct(
        #[Input] string $productId,      // Immanent
        #[Input] int $quantity,          // Immanent
        #[Inject] PriceCalculator $calc  // Transcendent
    ) {
        $this->total = $calc->calculate($productId, $quantity);
        $this->status = 'processed';
    }
}

$becoming = new Becoming($injector);
$result = $becoming(new OrderInput('PROD-123', 2));
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
