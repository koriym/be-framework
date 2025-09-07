# 1. Input クラス

Input クラスは Be Framework におけるすべての変換の出発点です。これらは純粋な**内在的**性質を持っています。つまり、すでに存在するもの、その本質的なアイデンティティです。

## 基本構造

```php
#[Be(UserProfile::class)]  // 変容の運命
final class UserInput
{
    public function __construct(
        public readonly string $name,     // 内在的
        public readonly string $email     // 内在的
    ) {}
}
```

## 主な特徴

**純粋なアイデンティティ**: Input クラスは、オブジェクトが根本的に*何であるか*のみを含みます—外部依存はなく、複雑なロジックもありません。

**変容の運命**: `#[Be()]` 属性は、この入力が何になるかを宣言します。

**読み取り専用プロパティ**: すべてのデータは不変であり、変異するのではなく変換される固定アイデンティティを表します。

## 例

### 単純なデータ入力
```php
#[Be(OrderCalculation::class)]
final class OrderInput
{
    public function __construct(
        public readonly array $items,        // 内在的
        public readonly string $currency     // 内在的
    ) {}
}
```

### 複雑な構造化入力
```php
#[Be(PaymentProcessing::class)]
final class PaymentInput
{
    public function __construct(
        public readonly Money $amount,           // 内在的
        public readonly CreditCard $card,        // 内在的
        public readonly Address $billing         // 内在的
    ) {}
}
```

## 内在的の役割

Input クラスでは、すべてが**内在的**です。つまり、オブジェクトが変換に持ち込む本来の性質です。ここには**超越的**な力は含まれません。それらは後のBeing クラスで登場します。

これは変換方程式の「自己」部分を表します：
**内在的 + 超越的 → 新しい内在的**

Input クラスは基盤を提供します。つまり、世界と出会い何か新しいものになる「すでに存在するもの」なのです。