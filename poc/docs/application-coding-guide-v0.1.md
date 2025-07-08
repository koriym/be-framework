# Ray.Framework アプリケーション コーディングガイド v0.1

> Ray.Frameworkでアプリケーションを構築する際の実践的な指針

## 基本原則

### 1. すべてはMetamorphosis Class

Ray.Frameworkでは、すべてのクラスがMetamorphosis Classです。各クラスは以下の特徴を持ちます：

```php
#[Be(NextClass::class)]  // 必須：次の変換先を宣言
final class MyClass      // 推奨：finalで継承を防ぐ
{
    public function __construct(
        #[Input] public readonly string $data,  // 前のオブジェクトから
        #[Inject] ServiceInterface $service     // DIコンテナから
    ) {
        // 1. バリデーション
        if (empty($this->data)) {
            throw new InvalidArgumentException('Data cannot be empty');
        }
        
        // 2. 変換処理
        $this->processedData = $service->process($this->data);
    }
    
    public readonly string $processedData;
}
```

### 2. 「Describe Yourself (Well)」の実践

すべてのコンストラクターパラメーターは明示的に依存関係を宣言します：

```php
// ✅ Good: 明示的な属性宣言
public function __construct(
    #[Input] string $email,              // 前のオブジェクトから
    #[Inject] UserValidator $validator   // DIから
) {}

// ❌ Bad: 属性なし（実行時エラーになります）
public function __construct(
    string $email,
    UserValidator $validator
) {}
```

## クラス設計パターン

### 1. 入力クラス（Entry Point）

アプリケーションの開始点となるクラス：

```php
#[Be(ValidationAttempt::class)]
final class UserRegistrationInput
{
    public function __construct(
        #[Input] public readonly string $email,
        #[Input] public readonly string $password,
        #[Input] public readonly string $passwordConfirmation
    ) {
        // バリデーションなし - 生データを受け入れ
    }
}
```

**特徴**:
- #[Input] のみ使用
- バリデーション処理は含まない
- 純粋なデータコンテナ

### 2. バリデーションクラス

データの妥当性を検証し、運命を決定するクラス：

```php
#[Be([ProcessedUser::class, ValidationError::class])]
final class UserValidation
{
    public readonly ValidUser|InvalidUser $being;

    public function __construct(
        #[Input] public readonly string $email,
        #[Input] public readonly string $password,
        #[Input] string $passwordConfirmation,
        #[Inject] UserValidator $validator
    ) {
        // 1. 入力値検証
        $validator->validateEmail($this->email);
        $validator->validatePassword($this->password);
        $validator->validatePasswordMatch($this->password, $passwordConfirmation);
        
        // 2. 存在による分岐決定
        $this->being = $someBusinessCondition
            ? new ValidUser($this->email, $this->password)
            : new InvalidUser($validator->getErrors());
    }
}
```

**重要な点**:
- 複数の変換先を#[Be]で宣言
- `$being` プロパティで運命を決定
- Union typesで型安全な分岐

### 3. 処理クラス

ビジネスロジックを実行するクラス：

```php
#[Be(UserCreated::class)]
final class UserProcessor
{
    public function __construct(
        #[Input] ValidUser $being,  // 前のオブジェクトから確実に受け取れる
        #[Inject] PasswordHasher $hasher,
        #[Inject] UserRepository $repository
    ) {
        // 1. パスワードハッシュ化
        $hashedPassword = $hasher->hash($being->password);
        
        // 2. ユーザー作成
        $this->userId = $repository->create($being->email, $hashedPassword);
        
        // 3. 追加処理...
    }
    
    public readonly string $userId;
}
```

### 4. 終端クラス

変換チェーンの最終段階：

```php
// #[Be] 属性なし = 変換終了
final class JsonResponse
{
    public function __construct(
        #[Input] object $payloadObject,
        int $statusCode = 200
    ) {
        $this->json = json_encode(get_object_vars($payloadObject));
        $this->statusCode = $statusCode;
    }
    
    public readonly string $json;
    public readonly int $statusCode;
}
```

## ネーミング規約

### クラス名

```php
// ✅ Good: 状態や役割を表現
UserInput           // 入力段階
ValidationAttempt   // 検証試行
ValidatedUser       // 検証済みユーザー
ProcessedOrder      // 処理済み注文
EmailSent           // メール送信完了

// ❌ Bad: 汎用的すぎる
Data
Result
Output
```

### プロパティ名

```php
// ✅ Good: 具体的で意味のある名前
public readonly string $hashedPassword;
public readonly UserProfile $profile;
public readonly ProcessingResult $result;

// ❌ Bad: 抽象的
public readonly string $data;
public readonly object $thing;
```

## エラーハンドリング

### 1. 存在による検証

```php
// ✅ Good: オブジェクトの存在 = 成功
try {
    $validated = new ValidatedUser($email, $password, $validator);
    // ここに到達 = 検証成功
} catch (ValidationException $e) {
    // 検証失敗
}

// ❌ Bad: ブール値による状態管理
if ($user->isValid()) {
    // ...
}
```

### 2. 型による分岐

```php
// ✅ Good: 型システムを活用
if ($result instanceof SuccessfulProcess) {
    // 成功処理
} elseif ($result instanceof FailedProcess) {
    // 失敗処理
}

// ❌ Bad: マジックナンバーやフラグ
if ($result->status === 'success') {
    // ...
}
```

## DIコンテナの活用

### 1. インターフェース分離

```php
// ✅ Good: 具体的な責務のインターフェース
interface EmailValidator
{
    public function isValidFormat(string $email): bool;
}

interface PasswordHasher
{
    public function hash(string $password): string;
}

// ❌ Bad: 巨大なインターフェース
interface UserService
{
    public function validateEmail(string $email): bool;
    public function hashPassword(string $password): string;
    public function sendEmail(string $to, string $subject): void;
    // ... 20個のメソッド
}
```

### 2. #[Named]属性の活用

```php
public function __construct(
    #[Input] string $content,
    #[Inject, Named('smtp')] MailerInterface $mailer,  // SMTP専用
    #[Inject, Named('debug')] string $logLevel         // デバッグレベル
) {}
```

## テスタブルな設計

### 1. 統合テスト優先

```php
class UserRegistrationFlowTest extends TestCase
{
    public function testSuccessfulRegistration(): void
    {
        // 完全な変換チェーンをテスト
        $injector = new Injector(new TestModule());
        $ray = new Ray($injector);
        
        $response = $ray(new UserRegistrationInput(
            'user@example.com',
            'SecurePass123!',
            'SecurePass123!'
        ));
        
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(201, $response->statusCode);
    }
}
```

### 2. モックは最小限に

```php
// ✅ Good: 実際のオブジェクトでテスト
$validator = new EmailValidator();
$hasher = new BcryptPasswordHasher();

// ❌ Bad: 過度なモック（必要な場合のみ）
$validator = $this->createMock(EmailValidator::class);
```

## パフォーマンス考慮事項

### 1. 重い処理の分離

```php
// ✅ Good: 重い処理は専用クラスで
#[Be(ThumbnailGenerated::class)]
final class ImageProcessor
{
    public function __construct(
        #[Input] UploadedImage $image,
        #[Inject] ImageService $service
    ) {
        $this->thumbnail = $service->generateThumbnail($image->path);
    }
}

// ❌ Bad: 軽い処理と重い処理の混在
final class UserWithAvatar
{
    public function __construct(/* 重い画像処理 + 軽いユーザー作成 */) {}
}
```

### 2. 遅延評価の検討

```php
final class ReportGenerator
{
    public function __construct(
        #[Input] ReportRequest $request,
        #[Inject] ReportService $service
    ) {
        // 重い処理は実際に必要になるまで遅延
        $this->reportGenerator = fn() => $service->generate($request);
    }
}
```

## コード例：実践的なユーザー登録フロー

```php
// 1. 入力
#[Be(UserValidation::class)]
final class UserRegistrationInput
{
    public function __construct(
        #[Input] public readonly string $email,
        #[Input] public readonly string $password,
        #[Input] public readonly string $passwordConfirmation
    ) {}
}

// 2. 検証と分岐
#[Be([UserCreation::class, RegistrationError::class])]
final class UserValidation
{
    public readonly ValidUserData|InvalidUserData $being;

    public function __construct(
        #[Input] public readonly string $email,
        #[Input] public readonly string $password,
        #[Input] string $passwordConfirmation,
        #[Inject] UserValidator $validator,
        #[Inject] UserRepository $repository
    ) {
        $validator->validateEmailFormat($this->email);
        $validator->validatePasswordStrength($this->password);
        $validator->validatePasswordMatch($this->password, $passwordConfirmation);

        $this->being = $repository->emailExists($this->email)
            ? new InvalidUserData('Email already registered')
            : new ValidUserData($this->email, $this->password);
    }
}

// 3a. 成功パス
#[Be(WelcomeEmailSent::class)]
final class UserCreation
{
    public function __construct(
        #[Input] ValidUserData $being,
        #[Inject] PasswordHasher $hasher,
        #[Inject] UserRepository $repository
    ) {
        $hashedPassword = $hasher->hash($being->password);
        $this->userId = $repository->create($being->email, $hashedPassword);
    }
    
    public readonly string $userId;
}

// 3b. エラーパス
#[Be(JsonResponse::class)]
final class RegistrationError
{
    public function __construct(
        #[Input] InvalidUserData $being
    ) {
        $this->error = $being->message;
        $this->statusCode = 400;
    }
    
    public readonly string $error;
    public readonly int $statusCode;
}

// 4. 終端
final class JsonResponse
{
    public function __construct(
        #[Input] object $payload,
        int $statusCode = 200
    ) {
        $this->json = json_encode(get_object_vars($payload));
        $this->statusCode = $statusCode;
    }
    
    public readonly string $json;
    public readonly int $statusCode;
}
```

## よくある間違いと対策

### 1. 属性の忘れ

```php
// ❌ エラー例
public function __construct(
    string $email,  // 属性なし
    UserValidator $validator
) {}

// ✅ 正しい書き方
public function __construct(
    #[Input] string $email,
    #[Inject] UserValidator $validator
) {}
```

### 2. 可変状態の導入

```php
// ❌ Bad: 可変プロパティ
public string $status = 'pending';

public function process(): void
{
    $this->status = 'completed';  // 状態変更
}

// ✅ Good: イミュータブル + 新しいオブジェクト
#[Be(CompletedProcess::class)]
final class PendingProcess
{
    public function __construct(/* ... */) {}
}

final class CompletedProcess
{
    public function __construct(
        #[Input] PendingProcess $previous,
        /* ... */
    ) {}
}
```

### 3. 複雑なコンストラクター

```php
// ❌ Bad: 複雑すぎるコンストラクター
public function __construct(/* 10個のパラメーター */)
{
    // 100行のロジック
}

// ✅ Good: 責務を分割
#[Be(ProcessedUser::class)]
final class ValidatedUser
{
    public function __construct(/* シンプルな検証のみ */) {}
}

#[Be(EnrichedUser::class)]
final class ProcessedUser
{
    public function __construct(/* 処理ロジック */) {}
}
```

## チェックリスト

アプリケーション開発時の確認事項：

- [ ] すべてのクラスにfinalキーワードを付けている
- [ ] すべてのプロパティがpublic readonlyである
- [ ] コンストラクターパラメーターに#[Input]または#[Inject]属性を付けている
- [ ] #[Be]属性で次の変換先を明示している
- [ ] バリデーションはコンストラクターで行っている
- [ ] エラー時は例外を投げている
- [ ] クラス名が状態や役割を明確に表現している
- [ ] 重い処理と軽い処理を適切に分離している
- [ ] 統合テストで変換チェーン全体をテストしている

---

*このガイドはv0.1として、実際の開発体験を通じて継続的に改善されます。*