# Be Framework 命名規約

> 哲学としてのコード：行為ではなく存在を反映する名前

このドキュメントは、Be Framework の存在論的プログラミング原理に合致する命名規約を確立し、**行う**ことではなく**存在する**ことを表現するコードを保証します。

## 中核となる哲学

**「オブジェクトは何かをするのではない—本来あるべき姿になる」**

私たちの命名は、命令的思考から実存的思考への根本的なシフトを反映します：
- **行為指向**の名前から → **存在指向**の名前へ
- **何をするか**から → **何であるか**へ
- **制御**から → **存在**へ

## クラス命名パターン

### Input クラス
**パターン**: `{ドメイン}Input`
**目的**: 変容の出発点を表現する純粋なデータコンテナ

```php
// ✅ 正しい
final class UserInput
final class OrderInput  
final class DataInput
final class PaymentInput

// ❌ 避ける
final class UserData          // 一般的すぎる
final class CreateUserRequest // 行為指向
final class UserCommand       // 命令的思考
```

### Being クラス
**パターン**: `Being{ドメイン}` または `{ドメイン}Being`
**目的**: オブジェクトがその性質を発見する中間変換段階

```php
// ✅ 正しい - Being接頭辞（推奨）
final class BeingUser
final class BeingOrder
final class BeingData
final class BeingPayment

// ✅ 許容可能 - Being接尾辞
final class UserBeing
final class OrderBeing

// ❌ 避ける
final class UserValidator     // 行為指向
final class OrderProcessor    // 何をするかであって何であるかではない
final class DataTransformer   // 命令的思考
```

### Final オブジェクト
**パターン**: 最終状態を表現するドメイン固有の結果名
**目的**: 成功した完了を表現する完全に変換された存在

```php
// ✅ 正しい - 存在の状態
final class ValidatedUser
final class ProcessedOrder
final class Success
final class Failure
final class ApprovedLoan
final class RejectedApplication

// ❌ 避ける  
final class UserResponse      // 実装詳細
final class OrderResult       // 一般的
final class ProcessingOutput  // 行為指向
```

## プロパティ命名

### Being プロパティ
**パターン**: `public readonly {型1}|{型2} $being;`
**目的**: ユニオン型を通じてオブジェクトの運命を運ぶ

```php
// ✅ 正しい
public readonly Success|Failure $being;
public readonly ValidUser|InvalidUser $being;
public readonly ApprovedLoan|RejectedLoan $being;

// ❌ 避ける
public readonly mixed $result;      // 型固有ではない
public readonly object $outcome;    // 一般的すぎる
public readonly array $data;        // 行為指向
```

### 内在的プロパティ
**パターン**: 本来のアイデンティティを反映する記述的な名前
**目的**: オブジェクトがすでに何であるか

```php
// ✅ 正しい
public readonly string $email;
public readonly Money $amount;
public readonly UserId $userId;
public readonly \DateTimeImmutable $timestamp;

// ❌ 避ける
public readonly string $inputEmail;    // 冗長な接頭辞
public readonly Money $requestAmount;  // 行為指向
```

## パラメータ命名

### コンストラクタパラメータ
**パターン**: 内在的にはプロパティ名と一致、超越的には記述的

```php
// ✅ 正しい
public function __construct(
    #[Input] string $email,              // 内在的 - プロパティと一致
    #[Input] Money $amount,              // 内在的 - プロパティと一致  
    #[Inject] EmailValidator $validator, // 超越的 - 能力
    #[Inject] PaymentGateway $gateway    // 超越的 - 外部サービス
) {}

// ❌ 避ける
public function __construct(
    #[Input] string $userEmail,          // プロパティ名と異なる
    #[Input] Money $inputAmount,         // 冗長な接頭辞
    #[Inject] object $emailChecker,      // 記述的でない
    #[Inject] mixed $paymentService      // 型固有でない
) {}
```

## 属性の使用

### Be 属性
**パターン**: `#[Be(DestinyClass::class)]` または `#[Be([Option1::class, Option2::class])]`

```php
// ✅ 単一の運命
#[Be(BeingUser::class)]
final class UserInput

// ✅ 複数の運命  
#[Be([ValidatedUser::class, InvalidUser::class])]
final class BeingUser

// ❌ 避ける
#[Be(UserProcessor::class)]    // 行為指向
#[Be(HandleUser::class)]       // 命令的
```

### Input/Inject コメント
**パターン**: 常に哲学的コメントを含める

```php
// ✅ 正しい
public function __construct(
    #[Input] string $email,                // 内在的
    #[Inject] EmailValidator $validator    // 超越的
) {}

// ❌ 哲学が欠けている
public function __construct(
    #[Input] string $email,
    #[Inject] EmailValidator $validator
) {}
```

## ドメイン固有の例

### Eコマースドメイン
```php
// Input → Being → Final
ProductInput → BeingProduct → [ValidProduct, InvalidProduct]
OrderInput → BeingOrder → [ProcessedOrder, FailedOrder]  
PaymentInput → BeingPayment → [SuccessfulPayment, DeclinedPayment]
```

### ユーザー管理ドメイン
```php
// Input → Being → Final  
UserInput → BeingUser → [RegisteredUser, ConflictingUser]
LoginInput → BeingLogin → [AuthenticatedUser, FailedAuthentication]
ProfileInput → BeingProfile → [UpdatedProfile, InvalidProfile]
```

### データ処理ドメイン
```php
// Input → Being → Final
DataInput → BeingData → [ProcessedData, CorruptedData]
FileInput → BeingFile → [ValidatedFile, InvalidFile]
ConfigInput → BeingConfig → [LoadedConfig, MalformedConfig]
```

## 避けるべきアンチパターン

### 命令的命名
```php
// ❌ 行為指向
ProcessUser, ValidateOrder, TransformData
CreatePayment, HandleRequest, ExecuteCommand

// ✅ 存在指向  
BeingUser, BeingOrder, BeingData
BeingPayment, BeingRequest, BeingCommand
```

### 一般的命名
```php
// ❌ 一般的すぎる
Handler, Processor, Manager, Service, Util

// ✅ 具体的で意味のある
BeingUser, ValidatedOrder, ProcessedPayment
```

### 技術実装詳細
```php
// ❌ 実装に焦点
UserDTO, OrderVO, PaymentPOJO, DataObject

// ✅ ドメインに焦点
UserInput, BeingOrder, ProcessedPayment
```

## 命名チェックリスト

任意のクラスを命名する前に問う：

1. **存在の質問**: 「このオブジェクトは何を*行う*のではなく何を*存在する*か？」
2. **段階の質問**: 「これは変容のどの段階を表現するか？」
3. **哲学の質問**: 「この名前は存在論的思考を反映しているか？」
4. **明確性の質問**: 「開発者はオブジェクトの性質を理解するか？」
5. **一貫性の質問**: 「これは確立されたパターンに従っているか？」

## 名前の進化

ドメインの理解が深まるにつれて、名前は進化するかもしれません：

```php
// 初期の理解
UserValidator → 

// より深い理解  
BeingUser →

// 完全な存在論的明確性
BeingUser // 明確な内在的/超越的区別を持つ
```

---

*「Be Framework では、名前はラベルではありません—存在の宣言です。詩の言葉を選ぶように慎重に選択してください。なぜならそれらは、私たちが創造する現実について考える方法を形作るからです。」*