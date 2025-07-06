<?php

declare(strict_types=1);

/**
 * Basic Type-Driven Metamorphosis Demo - Ray.Framework
 *
 * This example demonstrates the core concept of Type-Driven Metamorphosis
 * where objects carry their own destiny through typed properties.
 *
 * Key concepts shown:
 * - Objects that discover their own nature
 * - Union types as destiny maps
 * - Type-driven branching without if-statements
 * - The elimination of external control flow
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Ray\Framework\Ray;
use Ray\Framework\SimpleInjector;
use Ray\Framework\Attribute\Input;
use Ray\Framework\Attribute\To;

// =============================================================================
// DESTINY TYPES
// =============================================================================

/**
 * Represents data that was successfully processed
 */
final class Success
{
    public function __construct(
        public readonly string $data,
        public readonly string $processedBy = 'DataProcessor'
    ) {}
}

/**
 * Represents data that failed to process
 */
final class Failure
{
    public function __construct(
        public readonly string $reason,
        public readonly string $originalData
    ) {}
}

// =============================================================================
// SUPPORTING SERVICES
// =============================================================================

class DataValidator
{
    public function isValid(string $data): bool
    {
        // Simple validation: data must not be empty and not contain 'invalid'
        return !empty($data) && !str_contains(strtolower($data), 'invalid');
    }

    public function getErrors(string $data): string
    {
        if (empty($data)) {
            return 'Data cannot be empty';
        }
        if (str_contains(strtolower($data), 'invalid')) {
            return 'Data contains forbidden content';
        }
        return 'Unknown validation error';
    }
}

class DataProcessor
{
    public function process(string $data): string
    {
        return strtoupper($data) . ' [PROCESSED]';
    }
}

// =============================================================================
// TYPE-DRIVEN METAMORPHOSIS CHAIN
// =============================================================================

/**
 * Stage 1: Raw input data
 */
#[To(ValidationAttempt::class)]
final class RawData
{
    public function __construct(
        #[Input] public readonly string $value
    ) {
        // Pure data container - no logic
    }
}

/**
 * Stage 2: Validation attempt that discovers its own destiny
 *
 * This demonstrates Type-Driven Metamorphosis:
 * - The object asks itself: "Who am I?"
 * - It uses union types to express possible futures
 * - No external routing needed - the type determines the path
 */
#[To([SuccessfulValidation::class, FailedValidation::class])]
final class ValidationAttempt
{
    public function __construct(
        #[Input] string $data,
        DataValidator $validator,
        DataProcessor $processor
    ) {
        // The existential question: Who am I?
        $this->being = $validator->isValid($data)
            ? new Success($processor->process($data))
            : new Failure($validator->getErrors($data), $data);
    }

    // I carry my destiny within me
    public readonly Success|Failure $being;
}

/**
 * Success path - final transformation
 */
final class SuccessfulValidation
{
    public readonly string $message;
    public readonly \DateTimeImmutable $timestamp;

    public function __construct(
        Success $being  // Parameter name matches the property name
    ) {
        $this->message = "Successfully processed: {$being->data}";
        $this->timestamp = new \DateTimeImmutable();
    }
}

/**
 * Failure path - final transformation
 */
final class FailedValidation
{
    public readonly string $message;
    public readonly string $originalData;
    public readonly \DateTimeImmutable $timestamp;

    public function __construct(
        Failure $being  // Parameter name matches the property name
    ) {
        $this->message = "Processing failed: {$being->reason}";
        $this->originalData = $being->originalData;
        $this->timestamp = new \DateTimeImmutable();
    }
}

// =============================================================================
// DEMO EXECUTION
// =============================================================================

echo "=== Type-Driven Metamorphosis Demo ===\n\n";

// Set up dependency injection
$injector = new SimpleInjector();
$injector->singleton(DataValidator::class, new DataValidator());
$injector->singleton(DataProcessor::class, new DataProcessor());

// Create Ray executor
$ray = new Ray($injector);

// Demo 1: Successful processing
echo "Demo 1: Valid Data\n";
echo "Input: 'hello world'\n";

$result1 = $ray(new RawData('hello world'));
echo "Status: {$result1->status}\n";
echo "Message: {$result1->message}\n";
echo "Timestamp: " . $result1->timestamp->format('Y-m-d H:i:s') . "\n\n";

// Demo 2: Failed processing
echo "Demo 2: Invalid Data\n";
echo "Input: 'invalid data'\n";

$result2 = $ray(new RawData('invalid data'));
echo "Status: {$result2->status}\n";
echo "Message: {$result2->message}\n";
echo "Original Data: {$result2->originalData}\n";
echo "Timestamp: " . $result2->timestamp->format('Y-m-d H:i:s') . "\n\n";

// Demo 3: Empty data
echo "Demo 3: Empty Data\n";
echo "Input: ''\n";

$result3 = $ray(new RawData(''));
echo "Status: {$result3->status}\n";
echo "Message: {$result3->message}\n";
echo "Original Data: '{$result3->originalData}'\n";
echo "Timestamp: " . $result3->timestamp->format('Y-m-d H:i:s') . "\n\n";

echo "=== Key Insights ===\n";
echo "1. No if-statements in the flow logic\n";
echo "2. Objects discover their own nature through the \$being property\n";
echo "3. Union types (Success|Failure) express all possible destinies\n";
echo "4. Framework automatically routes based on types\n";
echo "5. Each transformation is pure and predictable\n\n";

echo "=== Transformation Paths ===\n";
echo "RawData -> ProcessingAttempt -> SuccessResponse (if valid)\n";
echo "RawData -> ProcessingAttempt -> FailureResponse (if invalid)\n";
echo "\nThe path is determined by existential self-discovery, not external control!\n";
