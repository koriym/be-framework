# Type-Driven Metamorphosis: Advanced Patterns

This reference provides comprehensive examples of Type-Driven Metamorphosis implementation patterns.

## Business Rule-Driven Metamorphosis

```php
#[Be([PremiumOrder::class, StandardOrder::class, BulkOrder::class])]
final class OrderClassification
{
    public readonly PremiumOrder|StandardOrder|BulkOrder $being;
    
    public function __construct(
        #[Input] Money $amount,
        #[Input] int $quantity,
        #[Input] bool $isPremiumCustomer,
        #[Inject] PricingService $pricing
    ) {
        $this->being = match (true) {
            $isPremiumCustomer && $amount->greaterThan(Money::USD(1000)) 
                => new PremiumOrder($amount, $quantity, $pricing->premiumDiscount()),
            $quantity > 50 
                => new BulkOrder($amount, $quantity, $pricing->bulkDiscount()),
            default 
                => new StandardOrder($amount, $quantity)
        };
    }
}
```

## Validation-Based Metamorphosis

```php
#[Be([ValidatedInput::class, InvalidInput::class])]
final class InputValidation
{
    public readonly ValidatedInput|InvalidInput $being;
    
    public function __construct(
        #[Input] string $email,
        #[Input] string $password,
        #[Inject] ValidationService $validator
    ) {
        $errors = $validator->validate($email, $password);
        
        $this->being = empty($errors)
            ? new ValidatedInput($email, $password)
            : new InvalidInput($errors);
    }
}
```

## Multi-Step Approval Process

```php
#[Be([AutoApproved::class, ManualReview::class, RejectedApplication::class])]
final class ApplicationProcessor
{
    public readonly AutoApproved|ManualReview|RejectedApplication $being;
    
    public function __construct(
        #[Input] CreditScore $score,
        #[Input] IncomeVerification $income,
        #[Input] DebtToIncomeRatio $ratio,
        #[Inject] RiskAssessment $risk
    ) {
        $riskLevel = $risk->assess($score, $income, $ratio);
        
        $this->being = match (true) {
            $riskLevel->isLow() && $score->isExcellent() 
                => new AutoApproved($score, $income, $riskLevel),
            $riskLevel->isMedium() || $score->isGood()
                => new ManualReview($score, $income, $ratio, $riskLevel),
            default 
                => new RejectedApplication($riskLevel->getReasons())
        };
    }
}
```

## Time-Based Metamorphosis

```php
#[Be([ActiveSession::class, ExpiredSession::class, SuspendedSession::class])]
final class SessionValidator
{
    public readonly ActiveSession|ExpiredSession|SuspendedSession $being;
    
    public function __construct(
        #[Input] SessionToken $token,
        #[Input] DateTime $lastActivity,
        #[Input] DateTime $now,
        #[Inject] SessionRepository $repository
    ) {
        $session = $repository->findByToken($token);
        $minutesSinceActivity = $now->diff($lastActivity)->i;
        
        $this->being = match (true) {
            $session->isSuspended() 
                => new SuspendedSession($session->getSuspensionReason()),
            $minutesSinceActivity > 30 
                => new ExpiredSession($lastActivity, $now),
            default 
                => new ActiveSession($session, $now)
        };
    }
}
```

## Complex Permission System

```php
#[Be([AdminAccess::class, UserAccess::class, GuestAccess::class, DeniedAccess::class])]
final class AccessControl
{
    public readonly AdminAccess|UserAccess|GuestAccess|DeniedAccess $being;
    
    public function __construct(
        #[Input] UserId $userId,
        #[Input] ResourceId $resourceId,
        #[Input] ActionType $action,
        #[Inject] PermissionService $permissions,
        #[Inject] UserRepository $users
    ) {
        $user = $users->find($userId);
        $hasPermission = $permissions->check($user, $resourceId, $action);
        
        $this->being = match (true) {
            $user->isAdmin() && $hasPermission 
                => new AdminAccess($user, $resourceId, $action),
            $user->isRegistered() && $hasPermission 
                => new UserAccess($user, $resourceId, $action),
            $user->isGuest() && $action->isReadOnly() && $hasPermission 
                => new GuestAccess($resourceId, $action),
            default 
                => new DeniedAccess($user, $resourceId, $action, $permissions->getDenialReason())
        };
    }
}
```

## Geographic Routing

```php
#[Be([DomesticShipping::class, InternationalShipping::class, RestrictedShipping::class])]
final class ShippingRouter
{
    public readonly DomesticShipping|InternationalShipping|RestrictedShipping $being;
    
    public function __construct(
        #[Input] Address $origin,
        #[Input] Address $destination,
        #[Input] ProductType $product,
        #[Inject] ShippingRules $rules,
        #[Inject] GeographicService $geo
    ) {
        $distance = $geo->calculateDistance($origin, $destination);
        $isInternational = $geo->isDifferentCountry($origin, $destination);
        $isRestricted = $rules->isProductRestricted($product, $destination->country);
        
        $this->being = match (true) {
            $isRestricted 
                => new RestrictedShipping($product, $destination, $rules->getRestrictions()),
            $isInternational 
                => new InternationalShipping($origin, $destination, $distance, $product),
            default 
                => new DomesticShipping($origin, $destination, $distance, $product)
        };
    }
}
```

## Financial Transaction Processing

```php
#[Be([ProcessedPayment::class, PendingPayment::class, FailedPayment::class, FraudAlert::class])]
final class PaymentProcessor
{
    public readonly ProcessedPayment|PendingPayment|FailedPayment|FraudAlert $being;
    
    public function __construct(
        #[Input] PaymentMethod $method,
        #[Input] Money $amount,
        #[Input] MerchantId $merchantId,
        #[Inject] FraudDetection $fraud,
        #[Inject] PaymentGateway $gateway
    ) {
        $fraudScore = $fraud->analyze($method, $amount, $merchantId);
        
        if ($fraudScore->isHigh()) {
            $this->being = new FraudAlert($method, $amount, $fraudScore);
            return;
        }
        
        try {
            $result = $gateway->process($method, $amount);
            
            $this->being = match ($result->status) {
                'completed' => new ProcessedPayment($result, $amount, $method),
                'pending' => new PendingPayment($result, $amount, $method),
                default => new FailedPayment($result->error, $amount, $method)
            };
        } catch (GatewayException $e) {
            $this->being = new FailedPayment($e->getMessage(), $amount, $method);
        }
    }
}
```

## Content Moderation

```php
#[Be([ApprovedContent::class, FlaggedContent::class, RejectedContent::class])]
final class ContentModerator
{
    public readonly ApprovedContent|FlaggedContent|RejectedContent $being;
    
    public function __construct(
        #[Input] UserContent $content,
        #[Input] UserId $authorId,
        #[Inject] ModerationService $moderation,
        #[Inject] UserRepository $users
    ) {
        $author = $users->find($authorId);
        $analysis = $moderation->analyze($content);
        
        $this->being = match (true) {
            $analysis->hasExplicitContent() || $analysis->hasSpam() 
                => new RejectedContent($content, $analysis->getViolations()),
            $analysis->isSuspicious() || $author->hasLowReputation() 
                => new FlaggedContent($content, $analysis->getFlags()),
            default 
                => new ApprovedContent($content, $analysis->getScore())
        };
    }
}
```

## Metamorphosis Continuation Examples

```php
// Multi-stage processing pipeline
$orderInput = new OrderInput($customerData, $items, $paymentInfo);

// Stage 1: Order validation and classification
$classifiedOrder = $becoming($orderInput);
// Results in OrderClassification with being property of type PremiumOrder|StandardOrder|BulkOrder

// Stage 2: Payment processing based on order type
$processedPayment = $becoming($classifiedOrder);
// Framework automatically uses the being property from classifiedOrder
// Results in PaymentProcessor with being property of type ProcessedPayment|PendingPayment|FailedPayment

// Stage 3: Shipping arrangement based on payment result
$shippingArrangement = $becoming($processedPayment);
// Results in ShippingRouter with being property of type DomesticShipping|InternationalShipping|RestrictedShipping

// Final processing based on shipping type
match (true) {
    $shippingArrangement->being instanceof DomesticShipping 
        => $this->scheduleDomesticDelivery($shippingArrangement->being),
    $shippingArrangement->being instanceof InternationalShipping 
        => $this->scheduleInternationalDelivery($shippingArrangement->being),
    $shippingArrangement->being instanceof RestrictedShipping 
        => $this->handleRestrictedItem($shippingArrangement->being),
};
```

## Error Recovery as Valid Beings

```php
#[Be([SuccessfulMigration::class, PartialMigration::class, FailedMigration::class])]
final class DataMigrator
{
    public readonly SuccessfulMigration|PartialMigration|FailedMigration $being;
    
    public function __construct(
        #[Input] DataSource $source,
        #[Input] DataTarget $target,
        #[Inject] MigrationService $service
    ) {
        $results = [];
        $errors = [];
        
        foreach ($source->getRecords() as $record) {
            try {
                $results[] = $service->migrate($record, $target);
            } catch (MigrationException $e) {
                $errors[] = $e;
            }
        }
        
        $this->being = match (true) {
            empty($errors) 
                => new SuccessfulMigration($results, count($results)),
            count($errors) < count($results) 
                => new PartialMigration($results, $errors),
            default 
                => new FailedMigration($errors, $source->getRecordCount())
        };
    }
}
```