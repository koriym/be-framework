# Ray.Framework POC 技術的判断と設計決定

> 実装過程で下した重要な技術的判断とその理由

## アーキテクチャ上の決定

### 1. InputQuery からの離脱

**決定**: Ray.InputQuery から独立した BecomingArguments 実装を採用

**背景**:
```php
// InputQuery の問題
$args = $inputQuery($current, $becoming);
// => ['result' => 'hello world']  // オブジェクトが文字列にフラット化

// 期待される動作
// => ['result' => FakeResult object]  // オブジェクトをそのまま保持
```

**理由**:
- オブジェクトプロパティの完全性保持が必要
- Ray.Framework の「存在による設計」哲学との整合性
- 将来的な拡張性の確保

**トレードオフ**: 開発時間の増加 vs 設計純度の向上 → 後者を選択

### 2. 属性の強制検証

**決定**: #[Input] または #[Inject] のいずれかを必須とする

```php
private function validateParameterAttributes(ReflectionParameter $param): void
{
    $hasInput = !empty($param->getAttributes(Input::class));
    $hasInject = !empty($param->getAttributes(Inject::class));

    if (!$hasInput && !$hasInject) {
        throw new InvalidArgumentException(/* ... */);
    }
}
```

**理由**:
- 「Describe Yourself (Well)」哲学の実現
- 暗黙的な依存関係の排除
- コードの自己文書化の促進

**実装の複雑さ**: 中程度（リフレクション API の活用が必要）

### 3. Union Types による分岐制御

**決定**: 条件分岐の代わりに型システムを活用

```php
public readonly Success|Failure $being;

// フレームワークが型に基づいて自動ルーティング
// if文による制御フローの排除
```

**利点**:
- 型安全性の向上
- 実行時エラーの削減
- IDE による静的解析サポート

**制約**: PHP 8.0+ の union types 機能が必要

## 実装技術の選択

### 1. ReflectionClass による動的インスタンス化

**実装**:
```php
private function metamorphose(object $current, string|array $becoming): object
{
    if (is_string($becoming)) {
        $args = ($this->becomingArguments)($current, $becoming);
        return (new ReflectionClass($becoming))->newInstanceArgs($args);
    }
    // ...
}
```

**他の選択肢と比較**:
- `new $becoming(...$args)` → 静的解析で警告
- Factory パターン → 過度な複雑化
- **ReflectionClass** → 型安全性と柔軟性のバランス

### 2. Named binding の Ray.Di 互換性

**実装**:
```php
// スカラー型での Named 属性処理
if ($type->isBuiltin() && $namedValue !== null) {
    return $this->injector->getInstance('', $namedValue);
}
```

**判断理由**:
- Ray.Di エコシステムとの互換性維持
- 既存コードの移行コスト削減
- 実装の簡素化

### 3. エラーハンドリング戦略

**採用方針**: 例外による早期失敗（Fail Fast）

```php
// 検証失敗時は即座に例外
if (empty($data)) {
    throw new InvalidArgumentException('Data cannot be empty');
}

// オブジェクトの存在 = 成功の証明
$validated = new ValidatedRegistration(/* ... */);
```

**利点**:
- デバッグの容易さ
- エラー状態の明確化
- 部分的失敗状態の回避

## パフォーマンス考慮事項

### 1. リフレクション使用の最適化

**課題**: リフレクション API のオーバーヘッド

**対策**:
```php
// 必要最小限のリフレクション処理
$targetClass = new ReflectionClass($becoming);
$constructor = $targetClass->getConstructor();

if ($constructor === null) {
    return [];  // 早期リターン
}
```

**将来の改善案**: 
- リフレクション結果のキャッシュ
- JIT コンパイラーでの最適化期待

### 2. メモリ効率の配慮

**設計方針**: イミュータブルオブジェクトによる安全性重視

```php
public readonly string $name;
public readonly string $email;
```

**トレードオフ**: メモリ使用量 vs 安全性 → 安全性を優先

## テスト戦略の技術的決定

### 1. 統合テスト中心のアプローチ

**方針**: ユニットテストよりも統合テストを重視

```php
public function testLinearMetamorphosis(): void
{
    // 完全な変換チェーンをテスト
    $input = new FakeValidatedUser('John', 'john@example.com', 25);
    $result = ($this->ray)($input);
    
    $this->assertInstanceOf(FakeActiveUser::class, $result);
}
```

**理由**: メタモルフィック・プログラミングの価値は変換チェーン全体にあるため

### 2. Mock を最小限に抑制

**決定**: 実際のオブジェクトを用いたテスト重視

```php
// Mockを使わずに実際のDI設定でテスト
$injector = new Injector(new class extends AbstractModule {
    protected function configure(): void {
        $this->bind(DataValidatorInterface::class)->to(SimpleValidator::class);
    }
});
```

**利点**: 実環境に近い条件でのテスト

## セキュリティ考慮事項

### 1. 動的クラスロードの制御

**潜在的リスク**: 任意クラスのインスタンス化

**対策**: 
```php
// Be属性による明示的な許可リスト
#[Be(ProcessedData::class)]  // 安全な変換先のみ許可
```

**追加考慮**: 本格実装では namespace 制限やクラス検証が必要

### 2. 入力値の検証

**方針**: コンストラクター段階での厳格な検証

```php
public function __construct(
    #[Input] public readonly string $email,
    #[Input] public readonly string $password,
    #[Inject] UserValidator $validator
) {
    $validator->validateEmailFormat($this->email);
    // 検証失敗時は例外 → オブジェクト自体が存在しない
}
```

## 今後の拡張における技術的考慮

### 1. 非同期処理への対応

**現在の制約**: 同期的な変換のみサポート

**将来の拡張案**:
```php
// AsyncRay の可能性
$promise = $asyncRay($input);
$result = await $promise;
```

### 2. 型システムの進化への対応

**現在**: Union types (PHP 8.0)

**将来**: Intersection types, Generics 等への対応検討

### 3. パフォーマンス最適化

**現在の実装**: 開発速度重視

**本格化時の考慮事項**:
- AOT コンパイル
- 変換チェーンの事前解析
- メモリプールの活用

## 学んだ教訓

### 1. 理論と実装の乖離

**発見**: 美しい理論も実装時に制約に直面する

**対処**: 哲学的一貫性を保ちつつ、実用的な妥協点を見つける

### 2. エコシステムとの調和

**重要性**: 既存ライブラリとの互換性は採用の鍵

**例**: Ray.Di との #[Named] 属性互換性

### 3. テストによる設計検証

**効果**: テストが設計の妥当性を証明

**具体例**: オブジェクトプロパティ継承テストが InputQuery の問題を露呈

---

*これらの技術的決定は、Ray.Framework の今後の発展における重要な基盤となります。*