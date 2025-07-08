モックはつくらないでください。
tests/Fakeクラスをつかってください。

# Ray.Framework コーディングガイド

## 基本原則

### 1. Do Not Manipulate - 他者を操作しない
- setterは作らない（オブジェクトは不変）
- 他のオブジェクトを変更するメソッドは作らない
- 外部状態管理はしない

### 2. Drive Yourself - 自分で自分を導く
- コンストラクタにすべての変換ロジックを含める
- オブジェクトは自分の運命を知っている（#[Be]）
- 型駆動メタモルフォーゼによる自己決定

### 3. Be Yourself - 自分自身であれ
- クラス名は存在を表現する（ValidatedOrder、OrderValidatorではない）
- プロパティは所有を表現し、制御ではない
- メソッド（もしあれば）は自己表現であり、他者への行動ではない

## 使用する名前
- **Metamorphosis** - 変換の行為
- **Being** - 何かであること、または何かになること
- **Morph** - 変化の瞬間
- **Ray** - 変換を導く光

## 使用しない名前
- ❌ **Manager** - オブジェクトは管理を必要としない
- ❌ **Resolver** - 何も解決する必要がない
- ❌ **Handler** - 処理ではなく、成ることのみ
- ❌ **Controller** - 制御ではなく、自然な流れ
- ❌ **Service** - サービスではなく、存在
- ❌ **Factory** - 製造ではなく、誕生
- ❌ **Strategy** - 戦略ではなく、運命
- ❌ **Helper** - 助けるものではなく、単純に存在するもの

## プロパティとメソッドの原則

### プロパティは public readonly
```php
class RegisteredUser {
    public function __construct(
        #[Input] public readonly string $id,
        #[Input] public readonly string $name,
        #[Input] public readonly string $email
    ) {
        // 誕生時に完全
        // すべてのプロパティは public readonly
        // メソッドは不要 - 単純に存在するのみ
    }
}
```

### アンチパターン
```php
// ❌ すべて間違い
class User {
    public function getId() {}        // 取得？ NO!
    public function setName() {}      // 設定？ NO!
    public function updateEmail() {}  // 更新？ NO!
    public function isValid() {}      // 尋ねる？ NO!
    public function toArray() {}      // 変換？ NO!
}
```

### なぜメソッドがないのか？
- `getId()` は誰かが私から「取得」したいことを暗示
- `setName()` は誰かが私に「設定」したいことを暗示
- `isValid()` は誰かが私の存在を疑っていることを暗示
- **存在することで私は有効**

## テスト原則

### モックは使用しない
- モックは作らない
- tests/Fakeクラスを使用する
- 実際のオブジェクトの振る舞いをテストする

### テストカバレッジ
- 100%のコードカバレッジを目指す
- 線形メタモルフォーゼをテスト
- 型駆動分岐をテスト（可能な成りの配列）
- 例外シナリオをテスト
- 空のメタモルフォーゼチェーンをテスト（#[Be]がない）

## 実装の美学

### 深い概念、シンプルな実装
Ray.Frameworkは深い哲学的概念をシンプルな実装で表現する。これは偶然ではなく、真の本質を見つけた証拠。

### コアの真理
```php
while ($becoming = ($this->getClass)($current)) {
    $current = $this->metamorphose($current, $becoming);
}
```

この2行で以下を表現：
- 存在論的プログラミング（存在駆動設計）
- 時間の不可逆性（一方向変換）
- 型駆動メタモルフォーゼ（自己決定）
- 成ることの永続的サイクル

### 複雑さの回避
複雑さを追加しようとしているなら、真理から遠ざかっている。フレームワークの美しさは禅のようなシンプルさにある。
