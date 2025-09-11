# 4. 変容パターン

Be Framework は、単純な線形チェーンから複雑な分岐する運命まで、様々な変換パターンをサポートしています。これらのパターンを理解することで、自然な変換フローを設計できます。

## 線形変容チェーン

最もシンプルなパターン：A → B → C → D

```php
// Input
#[Be(EmailValidation::class)]
final class EmailInput { /* ... */ }

// 最初の変換
#[Be(UserCreation::class)]
final class EmailValidation { /* ... */ }

// 二番目の変換  
#[Be(WelcomeMessage::class)]
final class UserCreation { /* ... */ }

// 最終結果
final class WelcomeMessage { /* ... */ }
```

各段階は海に流れる川のように、自然に次の段階へ導かれます。

## 分岐する運命

オブジェクトはその性質に基づいて複数の可能な未来を持つことができています：

```php
#[Be([ApprovedApplication::class, RejectedApplication::class])]
final class ApplicationReview
{
    public readonly ApprovedApplication|RejectedApplication $being;
    
    public function __construct(
        #[Input] array $documents,                // 内在的
        #[Inject] ReviewService $reviewer         // 超越的
    ) {
        $result = $reviewer->evaluate($documents);
        
        $this->being = $result->isApproved()
            ? new ApprovedApplication($documents, $result->getScore())
            : new RejectedApplication($result->getReasons());
    }
}
```

オブジェクトは**型駆動変容**を通じて自身の運命を決定しています。

## フォーク・ジョインパターン

単一の入力が並列変換に分岐し、後で収束します：

```php
#[Be(PersonalizedRecommendation::class)]
final class UserAnalysis
{
    public readonly PersonalizedRecommendation $being;
    
    public function __construct(
        #[Input] string $userId,                  // 内在的
        #[Inject] BehaviorAnalyzer $behavior,     // 超越的
        #[Inject] PreferenceAnalyzer $preference, // 超越的
        #[Inject] SocialAnalyzer $social          // 超越的
    ) {
        // 並列分析
        $behaviorScore = $behavior->analyze($userId);
        $preferenceScore = $preference->analyze($userId);
        $socialScore = $social->analyze($userId);
        
        // 収束
        $this->being = new PersonalizedRecommendation(
            $behaviorScore,
            $preferenceScore, 
            $socialScore
        );
    }
}
```

## 条件付き変換

時には変換が実行時条件に依存する場合があります：

```php
#[Be([PremiumFeatures::class, BasicFeatures::class])]
final class FeatureActivation
{
    public readonly PremiumFeatures|BasicFeatures $being;
    
    public function __construct(
        #[Input] User $user,                      // 内在的
        #[Inject] SubscriptionService $service    // 超越的
    ) {
        $subscription = $service->getSubscription($user);
        
        $this->being = $subscription->isPremium()
            ? new PremiumFeatures($user, $subscription)
            : new BasicFeatures($user);
    }
}
```

## ネストされた変容

複雑なオブジェクトは自身の変換チェーンを含むことができます：

```php
final class OrderProcessing
{
    public readonly PaymentResult $payment;
    public readonly ShippingResult $shipping;
    
    public function __construct(
        #[Input] Order $order,                    // 内在的
        #[Inject] Becoming $becoming              // 超越的
    ) {
        // ネストされた変換
        $this->payment = $becoming(new PaymentInput($order->getPayment()));
        $this->shipping = $becoming(new ShippingInput($order->getAddress()));
    }
}
```

## 自己組織化パイプライン

これらのパターンの美しさは、それらが**自己組織化**することです。オブジェクトは自身の運命を宣言し、フレームワークは外部オーケストレーションなしに自然に変換パスに従います。

```php
// コントローラもオーケストレータもない—ただ自然な流れ
$finalObject = $becoming(new ApplicationInput($documents));

// オブジェクトは本来あるべき姿になっている
match (true) {
    $finalObject->being instanceof ApprovedApplication => $this->sendApprovalEmail($finalObject->being),
    $finalObject->being instanceof RejectedApplication => $this->sendRejectionEmail($finalObject->being),
};
```

## パターン選択

ドメインの自然な流れに基づいてパターンを選択します：

- **線形**: 順次プロセス（検証 → 処理 → 完了）
- **分岐**: 決定点（承認/却下、成功/失敗）
- **フォーク・ジョイン**: 収束する並列分析
- **条件付き**: 機能フラグ、権限、サブスクリプション
- **ネスト**: サブプロセスを持つ複雑な操作

鍵は、変換をドメインロジックから自然に出現させることであり、人工的なパターンに強制しないことです。