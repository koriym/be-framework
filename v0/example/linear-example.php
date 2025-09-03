<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use Be\Framework\Attribute\Be;
use Be\Framework\Becoming;
use Ray\Di\Injector;
use Ray\InputQuery\Attribute\Input;

// Step 1: Raw order data
#[Be(ValidatedOrder::class)]
final class OrderInput
{
    public function __construct(
        public readonly string $productId,
        public readonly int $quantity,
        public readonly float $unitPrice
    ) {}
}

// Step 2: Validated order
#[Be(PricedOrder::class)]
final class ValidatedOrder
{
    public function __construct(
        #[Input] string $productId,
        #[Input] int $quantity,
        #[Input] float $unitPrice
    ) {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('Quantity must be positive');
        }
        if ($unitPrice <= 0) {
            throw new InvalidArgumentException('Unit price must be positive');
        }
        
        $this->productId = $productId;
        $this->quantity = $quantity;
        $this->unitPrice = $unitPrice;
        $this->isValid = true;
    }
    
    public readonly string $productId;
    public readonly int $quantity;
    public readonly float $unitPrice;
    public readonly bool $isValid;
}

// Step 3: Order with calculated price
#[Be(DiscountedOrder::class)]
final class PricedOrder
{
    public function __construct(
        #[Input] string $productId,
        #[Input] int $quantity,
        #[Input] float $unitPrice
    ) {
        $this->productId = $productId;
        $this->quantity = $quantity;
        $this->unitPrice = $unitPrice;
        $this->subtotal = $quantity * $unitPrice;
    }
    
    public readonly string $productId;
    public readonly int $quantity;
    public readonly float $unitPrice;
    public readonly float $subtotal;
}

// Step 4: Order with discount applied
#[Be(FinalizedOrder::class)]
final class DiscountedOrder
{
    public function __construct(
        #[Input] string $productId,
        #[Input] int $quantity,
        #[Input] float $subtotal
    ) {
        $this->productId = $productId;
        $this->quantity = $quantity;
        $this->subtotal = $subtotal;
        
        // Apply bulk discount
        if ($quantity >= 10) {
            $this->discount = 0.1; // 10% discount
        } elseif ($quantity >= 5) {
            $this->discount = 0.05; // 5% discount
        } else {
            $this->discount = 0;
        }
        
        $this->discountAmount = $subtotal * $this->discount;
        $this->total = $subtotal - $this->discountAmount;
    }
    
    public readonly string $productId;
    public readonly int $quantity;
    public readonly float $subtotal;
    public readonly float $discount;
    public readonly float $discountAmount;
    public readonly float $total;
}

// Step 5: Final order ready for processing
final class FinalizedOrder
{
    public function __construct(
        #[Input] string $productId,
        #[Input] float $total
    ) {
        $this->orderId = 'ORD-' . uniqid();
        $this->productId = $productId;
        $this->total = $total;
        $this->status = 'ready';
        $this->createdAt = date('Y-m-d H:i:s');
    }
    
    public readonly string $orderId;
    public readonly string $productId;
    public readonly float $total;
    public readonly string $status;
    public readonly string $createdAt;
}

// Execute the linear transformation
$becoming = new Becoming(new Injector());

echo "=== Be Framework: Linear Transformation Example ===\n\n";

// Create an order
$order = new OrderInput('PROD-123', 12, 29.99);

echo "Starting with OrderInput:\n";
echo "  Product: {$order->productId}\n";
echo "  Quantity: {$order->quantity}\n";
echo "  Unit Price: \${$order->unitPrice}\n\n";

// Transform through the chain
$result = $becoming($order);

echo "Final Result (FinalizedOrder):\n";
echo "  Order ID: {$result->orderId}\n";
echo "  Product ID: {$result->productId}\n";
echo "  Total: \${$result->total}\n";
echo "  Status: {$result->status}\n";
echo "  Created: {$result->createdAt}\n\n";

echo "Transformation chain:\n";
echo "  OrderInput → ValidatedOrder → PricedOrder → DiscountedOrder → FinalizedOrder\n";