# 2. Being クラス

Being クラスは変換が起こる場所です。前段階から**内在的**アイデンティティを受け取り、世界から**超越的**な力を受け取って、新しい存在形態を作り出します。

## 基本構造

```php
final class UserProfile
{
    public readonly string $displayName;
    public readonly bool $isValid;
    
    public function __construct(
        #[Input] string $name,                    // 内在的
        #[Input] string $email,                   // 内在的
        #[Inject] NameFormatter $formatter,       // 超越的
        #[Inject] EmailValidator $validator       // 超越的
    ) {
        $this->displayName = $formatter->format($name);     // 新しい内在的
        $this->isValid = $validator->validate($email);      // 新しい内在的
    }
}
```

## 変換パターン

すべてのBeing クラスは同じ存在論的パターンに従います：

**内在的** (`#[Input]`) + **超越的** (`#[Inject]`) → **新しい内在的**

- **内在的要因**: オブジェクトが前の形から継承するもの
- **超越的要因**: 世界によって提供される外部能力とコンテキスト
- **新しい内在的**: この相互作用から出現する変換された存在

## ワークショップとしてのコンストラクタ

コンストラクタは変容が起こる場所です。これは完全なワークショップで、そこでは以下が行われます：

1. アイデンティティが能力と出会う
2. 変換ロジックが存在する
3. 新しい不変の存在が出現する

```php
final class OrderCalculation
{
    public readonly Money $subtotal;
    public readonly Money $tax;
    public readonly Money $total;
    
    public function __construct(
        #[Input] array $items,                    // 内在的
        #[Input] string $currency,                // 内在的
        #[Inject] PriceCalculator $calculator,    // 超越的
        #[Inject] TaxService $taxService          // 超越的
    ) {
        $this->subtotal = $calculator->calculateSubtotal($items, $currency);
        $this->tax = $taxService->calculateTax($this->subtotal);
        $this->total = $this->subtotal->add($this->tax);     // 新しい内在的
    }
}
```

## Final オブジェクトへの橋渡し

Being クラスはしばしば橋渡しとして機能し、最終変換のためのデータを準備しています：

```php
#[Be([SuccessfulOrder::class, FailedOrder::class])]  // 複数の運命
final class OrderValidation
{
    public readonly bool $isValid;
    public readonly array $errors;
    public readonly SuccessfulOrder|FailedOrder $being;  // Being プロパティ
    
    public function __construct(
        #[Input] Money $total,                    // 内在的
        #[Input] CreditCard $card,                // 内在的
        #[Inject] PaymentGateway $gateway         // 超越的
    ) {
        $result = $gateway->validate($card, $total);
        $this->isValid = $result->isValid();
        $this->errors = $result->getErrors();
        
        // 運命の自己決定
        $this->being = $this->isValid 
            ? new SuccessfulOrder($total, $card)
            : new FailedOrder($this->errors);
    }
}
```

## 自然な流れ

Being クラスは何かを「行う」のではありません。世界の能力との相互作用を通じて、本来あるべき姿に自然になるのです。これは、強制的なコントロールのない自然な変換の原理を体現しています。