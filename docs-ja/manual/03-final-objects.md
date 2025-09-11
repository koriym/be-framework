# 3. Final オブジェクト

Final オブジェクトは変容の終着点を表しています。ユーザーの実際の関心を体現する完全で変換された存在です。これらはアプリケーションが最終的に関心を持つものです。

## Final オブジェクトの特徴

**完全な存在**: Final オブジェクトは、その意図された目的にとってこれ以上の変換を必要としない完全に形成されたエンティティです。

**ユーザー中心**: これらはユーザーが実際に求めるものを表現しています。成功した操作、意味のあるデータ、実行可能な結果などです。

**豊富な状態**: Input クラスとは異なり、Final オブジェクトは変換されたデータの完全な豊富さを含んでいます。

## 例

### 成功した結果
```php
final class SuccessfulOrder
{
    public readonly string $orderId;
    public readonly string $confirmationCode;
    public readonly DateTimeImmutable $timestamp;
    public readonly string $message;
    
    public function __construct(
        #[Input] Money $total,                    // 検証からの内在的
        #[Input] CreditCard $card,                // 検証からの内在的
        #[Inject] OrderIdGenerator $generator,    // 超越的
        #[Inject] Receipt $receipt                // 超越的
    ) {
        $this->orderId = $generator->generate();              // 新しい内在的
        $this->confirmationCode = $receipt->generate($total); // 新しい内在的
        $this->timestamp = new DateTimeImmutable();          // 新しい内在的
        $this->message = "Order confirmed: {$this->orderId}"; // 新しい内在的
    }
}
```

### Final オブジェクトとしてのエラー状態
```php
final class FailedOrder
{
    public readonly string $errorCode;
    public readonly string $message;
    public readonly DateTimeImmutable $timestamp;
    
    public function __construct(
        #[Input] array $errors,                   // 検証からの内在的
        #[Inject] Logger $logger,                 // 超越的
        #[Inject] ErrorCodeGenerator $generator   // 超越的
    ) {
        $this->errorCode = $generator->generate();
        $this->message = "Order failed: " . implode(', ', $errors);
        $this->timestamp = new DateTimeImmutable();
        
        $logger->logOrderFailure($this->errorCode, $errors);  // 副作用
    }
}
```

## Final オブジェクト vs Input クラス

| Input クラス | Final オブジェクト |
|---------------|---------------|
| 純粋なアイデンティティ | 豊富で変換された状態 |
| 出発点 | 終着点 |
| ユーザーが提供するもの | ユーザーが受け取るもの |
| 単純な構造 | 完全な機能性 |

## 複数の Final な運命

オブジェクトは、その性質によって決定される複数の可能な最終形態を持つことができています：

```php
// OrderValidation の being プロパティから：
public readonly SuccessfulOrder|FailedOrder $being;

// 使用法：
$order = $becoming(new OrderInput($items, $card));

if ($order->being instanceof SuccessfulOrder) {
    echo $order->being->confirmationCode;
} else {
    echo $order->being->message;  // エラーメッセージ
}
```

## 完成した旅

Input から Final オブジェクトまでの道は、完全な変換の旅を表しています：

1. **Input クラス**: 純粋なアイデンティティ（「私はこれです」）
2. **Being クラス**: 変換段階（「私はこのように変化します」）
3. **Final オブジェクト**: 完全な結果（「私はこうなりました」）

ユーザーは主にInput（提供するもの）とFinal オブジェクト（受け取るもの）に関心を持っています。その間のBeing クラスはフレームワークの責任です。意図と結果の間の橋を作る変換の仕組みです。

## 自然な完成

Final オブジェクトは自然な変換の完成を体現しています。これらはこれ以上何かを「行う」必要がありません。元の入力が世界の能力との出会いから出現することが意図された結果に単純に*なっている*のです。