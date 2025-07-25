# 4. Metamorphosis Patterns

Be Framework supports various patterns of transformation, from simple linear chains to complex branching destinies. Understanding these patterns helps you design natural transformation flows.

## Linear Metamorphic Chain

The simplest pattern: A → B → C → D

```php
// Input
#[Be(EmailValidation::class)]
final class EmailInput { /* ... */ }

// First transformation
#[Be(UserCreation::class)]
final class EmailValidation { /* ... */ }

// Second transformation  
#[Be(WelcomeMessage::class)]
final class UserCreation { /* ... */ }

// Final result
final class WelcomeMessage { /* ... */ }
```

Each stage naturally leads to the next, like a river flowing to the sea.

## Branching Destinies

Objects can have multiple possible futures based on their nature:

```php
#[Be([ApprovedApplication::class, RejectedApplication::class])]
final class ApplicationReview
{
    public readonly ApprovedApplication|RejectedApplication $being;
    
    public function __construct(
        #[Input] array $documents,                // Immanent
        #[Inject] ReviewService $reviewer         // Transcendent
    ) {
        $result = $reviewer->evaluate($documents);
        
        $this->being = $result->isApproved()
            ? new ApprovedApplication($documents, $result->getScore())
            : new RejectedApplication($result->getReasons());
    }
}
```

The object determines its own destiny through **Type-Driven Metamorphosis**.

## Fork-Join Pattern

A single input branches into parallel transformations that later converge:

```php
#[Be(PersonalizedRecommendation::class)]
final class UserAnalysis
{
    public readonly PersonalizedRecommendation $being;
    
    public function __construct(
        #[Input] string $userId,                  // Immanent
        #[Inject] BehaviorAnalyzer $behavior,     // Transcendent
        #[Inject] PreferenceAnalyzer $preference, // Transcendent
        #[Inject] SocialAnalyzer $social          // Transcendent
    ) {
        // Parallel analysis
        $behaviorScore = $behavior->analyze($userId);
        $preferenceScore = $preference->analyze($userId);
        $socialScore = $social->analyze($userId);
        
        // Convergence
        $this->being = new PersonalizedRecommendation(
            $behaviorScore,
            $preferenceScore, 
            $socialScore
        );
    }
}
```

## Conditional Transformation

Sometimes transformation depends on runtime conditions:

```php
#[Be([PremiumFeatures::class, BasicFeatures::class])]
final class FeatureActivation
{
    public readonly PremiumFeatures|BasicFeatures $being;
    
    public function __construct(
        #[Input] User $user,                      // Immanent
        #[Inject] SubscriptionService $service    // Transcendent
    ) {
        $subscription = $service->getSubscription($user);
        
        $this->being = $subscription->isPremium()
            ? new PremiumFeatures($user, $subscription)
            : new BasicFeatures($user);
    }
}
```

## Nested Metamorphosis

Complex objects can contain their own transformation chains:

```php
final class OrderProcessing
{
    public readonly PaymentResult $payment;
    public readonly ShippingResult $shipping;
    
    public function __construct(
        #[Input] Order $order,                    // Immanent
        #[Inject] Becoming $becoming              // Transcendent
    ) {
        // Nested transformations
        $this->payment = $becoming(new PaymentInput($order->getPayment()));
        $this->shipping = $becoming(new ShippingInput($order->getAddress()));
    }
}
```

## Self-Organizing Pipelines

The beauty of these patterns is that they're **self-organizing**. Objects declare their own destinies, and the framework naturally follows the transformation paths without external orchestration.

```php
// No controllers, no orchestrators—just natural flow
$result = $becoming(new ApplicationInput($documents));

// The object has become what it was meant to be
match (true) {
    $result->being instanceof ApprovedApplication => $this->sendApprovalEmail($result->being),
    $result->being instanceof RejectedApplication => $this->sendRejectionEmail($result->being),
};
```

## Pattern Selection

Choose patterns based on your domain's natural flow:

- **Linear**: Sequential processes (validation → processing → completion)
- **Branching**: Decision points (approve/reject, success/failure)
- **Fork-Join**: Parallel analysis that converges
- **Conditional**: Feature flags, permissions, subscriptions
- **Nested**: Complex operations with sub-processes

The key is to let the transformation emerge naturally from the domain logic, not force it into artificial patterns.