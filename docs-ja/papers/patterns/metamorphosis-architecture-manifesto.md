# メタモルフォーゼ・アーキテクチャ：設計マニフェスト

## 序文：実践的実装

このドキュメントは、存在論的プログラミング原則に基づいてメタモルフォーゼ・アーキテクチャを使用するシステム構築のための実践的実装ガイダンスを提供します。

> **哲学的基盤:** 存在論的プログラミングの理論的基盤については、[Ontological Programming: A New Paradigm](../philosophy/ontological-programming-paper.md) を参照してください。

**中核哲学:** 存在論的プログラミング  
**アーキテクチャ・パターン:** メタモルフォーゼ・アーキテクチャ

---

## 1. 中核原則：存在の法則

このアーキテクチャは、存在論的プログラミングから導出された妥協なき原則のセットに基づいています。

### 原則 1：存在はコンストラクタで定義される
オブジェクトが有効に存在するために必要なすべてのロジックは、そのコンストラクタ内に排他的に存在する。コンストラクタがエラーなしで完了すれば、オブジェクトは存在し、その存在がその有効性の証明である。

### 原則 2：Beingは不変
オブジェクトが一度存在すると、その状態は変更できない。それは完璧で、不変の事実である。すべてのプロパティは`public readonly`。変換は変更ではなく、新しい存在の創造である。

### 原則 3：クラスは単一の完璧な存在段階
各クラス——**メタモルフォーゼ・クラス**と呼ぶ——は、プロセスの一つの明確で、完全で、自己完結した段階を表現する。それは自分自身の存在のみを知り、前に何があったか、後に何が来るかは知らない。

### 原則 4：オブジェクトは運命を運ぶ
プロセスの進行は一連のメソッド呼び出しではなく、オブジェクトが`$being`プロパティを通じて何になる運命かを発見することである。

### 原則 5：型は分類ではなく可能性
ユニオン型は現在の分類ではなく潜在的未来を表現する。それらは分類の箱ではなく、運命の地図である。

---

## 2. メタモルフォーゼ・クラスの解剖学

メタモルフォーゼ・クラスは、このアーキテクチャの基本構成要素です。

```php
/**
 * メタモルフォーゼ・クラスは存在の単一の検証された状態を表現します。
 * その名前は何を*するか*ではなく、何で*あるか*を記述すべきです。
 *
 * 例：検証されたが、まだ保存されていないユーザー登録。
 */
final class ValidatedRegistration
{
    // 不変なpublicプロパティがこの存在の「本質」を定義します。
    public readonly string $email;
    public readonly string $password;

    /**
     * コンストラクタは存在の門です。
     * 「前世」（前段階）と自分を存在させるために必要な「ツール」
     * （依存関係）を受け取ります。
     */
    public function __construct(
        #[Input] RegistrationInput $rawInput,
        UserValidator $validator
    ) {
        // 存在条件：検証が通らなければならない。
        // 失敗すれば例外が投げられ、このオブジェクトは決して存在しない。
        $validator->validate($rawInput->email, $rawInput->password);

        // 検証が通れば、その本質が確立される。
        $this->email = $rawInput->email;
        $this->password = $rawInput->password;
    }
}
```

---

## 3. 型駆動メタモルフォーゼ：Beingプロパティ

線形パイプラインはシンプルですが、現実は条件付きパスを必要とします。中心的な設計問題は：将来について何も知らないメタモルフォーゼ・クラスが、どのパスを取るかをどう決定するかです。

答えは、存在論的プログラミングの最も深遠な革新にあります：**型駆動メタモルフォーゼ**。オブジェクトはPHPのユニオン型を使用して可能な未来を表現する特別なプロパティを通じて、自分自身の運命を運びます。

### 3.1 コードにおける実存的問い

外的制御フローの代わりに、内的自己決定があります。オブジェクトは分岐ロジックを実行しません；自分の性質を発見します。

```php
/**
 * 実存的問い：私は誰になるのか？
 * 答え：私は私の内に運命を運ぶ。
 */
#[Be([UnverifiedUser::class, UserConflict::class])]
final class ValidatedRegistration
{
    public function __construct(
        #[Input] public readonly string $email,
        #[Input] public readonly string $password,
        UserRepository $userRepo
    ) {
        // 実存的問い：私は誰になるのか？
        $this->being = $userRepo->existsByEmail($this->email)
            ? new ConflictingUser($this->email)
            : new NewUser($this->email, $this->password);
    }
    
    // 私は私の内に運命を運ぶ
    public readonly NewUser|ConflictingUser $being;
}
```

### 3.2 型駆動メタモルフォーゼの仕組み

オブジェクトが`#[Be]`属性を通じて複数の潜在的メタモルフォーゼを宣言するとき、フレームワークはbeingプロパティの型を調べて、どのパスを取るかを決定します：

- `$being instanceof NewUser`なら → `UnverifiedUser::class`
- `$being instanceof ConflictingUser`なら → `UserConflict::class`

これは存在論的プログラミングの最も純粋な形式を表現します：オブジェクトはシステムに何をすべきかを告げません；自分が誰であるかを発見します。

---

## 4. 型駆動システムのテスト

型駆動メタモルフォーゼをどうテストするか？我々は行動ではなく**型検証**に焦点を当てます。オブジェクトが正しい性質を発見することを検証します。

### 4.1 Beingプロパティのテスト

型駆動システムの美しさは、テストが副作用ではなく型について行われることです：

```php
public function testRegistrationBecomesNewUser(): void
{
    $mockRepo = $this->createMock(UserRepository::class);
    $mockRepo->method('existsByEmail')->willReturn(false);
    
    $registration = new ValidatedRegistration(
        'new@example.com',
        'password123',
        $mockRepo
    );
    
    // 行動ではなく型をアサート
    $this->assertInstanceOf(NewUser::class, $registration->being);
    $this->assertNotInstanceOf(ConflictingUser::class, $registration->being);
}

public function testRegistrationBecomesConflict(): void
{
    $mockRepo = $this->createMock(UserRepository::class);
    $mockRepo->method('existsByEmail')->willReturn(true);
    
    $registration = new ValidatedRegistration(
        'existing@example.com',
        'password123',
        $mockRepo
    );
    
    // 行動ではなく型をアサート
    $this->assertInstanceOf(ConflictingUser::class, $registration->being);
    $this->assertEquals('existing@example.com', $registration->being->email);
}
```

### 4.2 型ベーステストの純粋性

型駆動テストは不変名原則と存在ベースの思考に従います：

```php
public function testValidationBecomesSuccessful(): void
{
    $mockValidator = $this->createMock(DataValidator::class);
    $mockValidator->method('isValid')->willReturn(true);
    
    $attempt = new BeingData('valid-data', $mockValidator);
    
    // 行動ではなく型とプロパティ名をアサート
    $this->assertInstanceOf(Success::class, $attempt->being);
    $this->assertEquals('valid-data', $attempt->being->data);
}

public function testValidationBecomesFailed(): void
{
    $mockValidator = $this->createMock(DataValidator::class);
    $mockValidator->method('isValid')->willReturn(false);
    
    $attempt = new BeingData('invalid-data', $mockValidator);
    
    // 型——オブジェクトが何に成ったか——をアサート
    $this->assertInstanceOf(Failure::class, $attempt->being);
    $this->assertNotInstanceOf(Success::class, $attempt->being);
}
```

型駆動テストは：
- **実存的**：何をしたかではなく、何になったかを検証
- **宣言的**：何が存在すべきかを記述
- **純粋**：追跡すべき副作用なし
- **信頼性**：型は嘘をつかない
- **自己文書化**：テストが仕様である
- **不変名原則準拠**：命名の継続性をテスト

---

## 5. 運命の地図としてのユニオン型

型駆動メタモルフォーゼの力はPHPのユニオン型にあります。それらは**運命の地図**——オブジェクトが住むかもしれないすべての可能な未来の宣言——となります。

### 5.1 単純対複雑な運命

一部のオブジェクトはシンプルで予測可能な未来を持ちます：

```php
// 単純な運命——線形進行
#[Be([RetiredEmployee::class])]
final class SeniorEmployee {
    public readonly Pension $being;  // 単一の予測可能な未来
}
```

他のものは複雑な分岐現実に直面します：

```php
// 複雑な運命——複数の可能性
#[Be([Unicorn::class, Acquisition::class, Bankruptcy::class, Pivot::class])]
final class Startup {
    public readonly Success|Buyout|Failure|Transformation $being;
}
```

### 5.2 分類ではなく可能性としての型

従来のプログラミングでは、型は物事が何であるかを分類します。存在論的プログラミングでは、ユニオン型は物事が何に**なるかもしれない**かを表現します：

```php
// 従来：何であるか
final class User {
    public string $status; // "active", "suspended", "deleted"
}

// 存在論的：何になるかもしれないか
#[Be([ActiveUser::class, SuspendedUser::class, DeletedUser::class])]
final class UserAccount {
    public readonly Active|Suspended|Deleted $being;
}
```

---

## 6. 実装例：Eコマース注文処理

以下は実世界のEコマースシステムにおける型駆動メタモルフォーゼを実証し、異なる初期選択がどのように異なるレベルの複雑性に導くかを示します。

```php
/**
 * ------------------------------------------------------------------
 * ステージ1：注文分析——私は何か？
 * ------------------------------------------------------------------
 */

// 初期注文はその内容に基づいて多くのものになりうる
#[Be([DigitalOrder::class, PhysicalOrder::class, SubscriptionOrder::class])]
final class NewOrder
{
    public function __construct(
        #[Input] public readonly array $items,
        #[Input] public readonly CustomerId $customerId,
        OrderAnalyzer $analyzer
    ) {
        // 実存的問い：私はどんな種類の注文か？
        $this->being = $analyzer->categorize($this->items);
    }
    
    // 私の運命は私が含むもので決まる
    public readonly Digital|Physical|Subscription $being;
}

/**
 * ------------------------------------------------------------------
 * ステージ2：異なる道、異なる複雑性
 * ------------------------------------------------------------------
 */

// デジタル注文はシンプルで線形な進行——単一運命
#[Be([InstantDelivery::class])]
final class DigitalOrder
{
    public function __construct(
        #[Input] Digital $items,
        LicenseGenerator $generator
    ) {
        // シンプルな変換——私はダウンロード可能になる
        $this->being = new Downloadable(
            $items,
            $generator->generateLicenses($items)
        );
    }
    
    public readonly Downloadable $being;  // 単一の予測可能な未来
}

// 物理注文は複雑な現実に直面——複数運命
#[Be([
    StandardShipping::class,
    ExpressShipping::class,
    InternationalShipping::class,
    BackorderRequired::class
])]
final class PhysicalOrder
{
    public function __construct(
        #[Input] Physical $physicalItems,
        #[Input] CustomerId $customerId,
        InventoryChecker $inventory,
        ShippingCalculator $shipping,
        CustomerRepository $customers
    ) {
        $customer = $customers->find($customerId);
        
        // 複雑な実存分析
        if (!$inventory->available($physicalItems)) {
            $this->being = new Delayed($physicalItems);
        } elseif ($customer->location->isInternational()) {
            $this->being = new International($physicalItems, $customer->location);
        } else {
            $this->being = $shipping->determineMethod($physicalItems);
        }
    }
    
    // 現実に基づく複数の可能な未来
    public readonly Shippable|Delayed|International $being;
}

// サブスクリプションは独自のユニークな複雑性を持つ
#[Be([RecurringBilling::class, TrialPeriod::class])]
final class SubscriptionOrder
{
    public function __construct(
        #[Input] Subscription $subscription,
        #[Input] CustomerId $customerId,
        CustomerRepository $customers
    ) {
        $customer = $customers->find($customerId);
        
        // 初回対リピート顧客ロジック
        $this->being = $customer->hasActiveSubscriptions()
            ? new Immediate($subscription)
            : new Trial($subscription);
    }
    
    public readonly Immediate|Trial $being;
}

/**
 * ------------------------------------------------------------------
 * ステージ3：最終変換
 * ------------------------------------------------------------------
 */

// デジタル用のシンプルな完了
final class InstantDelivery
{
    public function __construct(
        #[Input] Downloadable $content,
        EmailService $mailer
    ) {
        $this->confirmationSent = $mailer->sendDownloadLinks($content);
        $this->completedAt = new DateTimeImmutable();
    }
    
    public readonly bool $confirmationSent;
    public readonly DateTimeImmutable $completedAt;
}

// 物理用の複雑な配送調整
final class StandardShipping
{
    public function __construct(
        #[Input] Shippable $items,
        ShippingService $shipper,
        TrackingService $tracking
    ) {
        $this->shipment = $shipper->createShipment($items);
        $this->trackingNumber = $tracking->generateNumber($this->shipment);
        $this->estimatedDelivery = $shipper->calculateDelivery($items);
    }
    
    public readonly Shipment $shipment;
    public readonly string $trackingNumber;
    public readonly DateTimeImmutable $estimatedDelivery;
}
```

### 6.1 創発的複雑性の美しさ

注目すべきは：
- **デジタル注文**がシンプルで線形な変換に導く
- **物理注文**が複雑な配送現実に分岐する
- **サブスクリプション**が独自のビジネスロジック複雑性を持つ

複雑性はアーキテクチャ決定からではなく、**存在するものの性質**から生まれます。

---

## 7. 型駆動マニフェスト

## 8. 型駆動マニフェスト

中核原則に基づいて、これらの追加真理を確立します：

1. **我々は分岐しない；成る**——制御フローは型駆動運命に置き換えられる

2. **型は分類ではない；可能性である**——ユニオン型は潜在的未来を表現する

3. **beingプロパティは神聖である**——オブジェクトの実存的答えを運ぶ

4. **複雑性は設計されない；生まれる**——シンプルな型選択から複雑システムが生じる

5. **すべてのオブジェクトは次の形を知っている**——手続きロジックではなく型宣言を通じて

6. **オブジェクトは運命を運ぶ**——未来は現在の内に生きる

7. **実存的問いが変換を駆動する**——「何をすべきか？」ではなく「私は誰か？」

8. **名前は変換を通じて本質を運ぶ**——プロパティ名がパラメータ名になり、アイデンティティを保存する

9. **クラスは行動ではなく存在を宣言する**——何を*するか*ではなく何で*あるか*を命名する

10. **不変名原則が継続性を統治する**——プロパティ名はメタモルフィック段階を流れる

### 8.1 実装原則

型駆動メタモルフォーゼを実装するとき：

**原則1：純粋型駆動運命**
```php
// 良い：不変名原則での純粋型駆動
final class BeingData {
    public readonly Success|Failure $being;
}

final class Success {
    public function __construct(Success $being) {} // 名前が一致！
}

// 悪い：隠された条件文
public function getBeing() {
    return $this->success ? new Success() : new Failure();
}
```

**原則2：存在ベース命名**
```php
// 良い：物事が何であるか
final class AuthenticatedUser
final class VerifiedEmail  
final class CompletedPayment

// 悪い：物事が何をするか
final class UserAuthenticator
final class EmailValidator
final class PaymentProcessor
```

**原則3：不変名原則の遵守**
```php
// 良い：プロパティ名の継承
class CurrentStage {
    public readonly TypeA|TypeB $destiny;
}
class NextStage {
    public function __construct(TypeA $destiny) {} // 同じ名前！
}

// 悪い：壊れた命名連鎖
class CurrentStage {
    public readonly TypeA|TypeB $result;
}
class NextStage {
    public function __construct(TypeA $input) {} // 異なる名前！
}
```

**原則4：可能性マップとしてのユニオン型**
```php
// 良い：明確な運命
public readonly Approved|Rejected|Pending|Escalated $being;

// 悪い：文字列ベースのルーティング  
public string $nextState = 'approved'; // 型安全性を失う
```

---

## 9. 結論：実存的発見としてのプログラミング

型駆動変換を持つメタモルフォーゼ・アーキテクチャは、プログラミングをどう理解するかの根本的シフトを表現します。オブジェクトに**命令する**ことから、その自己発見を**可能にする**ことへ移ります。

### 9.1 パラダイムの完成

型駆動メタモルフォーゼは、従来のプログラミングが常に求めてきたものを達成します：

- **条件複雑性の排除**——if文地獄の排除
- **完璧な型安全性**——運命マップとしてのユニオン型
- **自己文書化システム**——型がドキュメントである
- **創発的アーキテクチャ**——複雑性は設計からではなく存在から生じる

### 9.2 実行から存在へ：完全な旅

プログラミングの進化は理解の深化を反映します：

1. **命令型時代**：「XならばYをする」——機械的指示
2. **オブジェクト指向時代**：「XならばオブジェクトYが処理」——委任
3. **関数型時代**：「XをYに変換」——数学的純粋性
4. **存在論的時代**：「XはYであることを発見」——実存的自己決定

### 9.3 究極の問い

従来のプログラミングでは問います：「このオブジェクトは何をすべきか？」

存在論的プログラミングでは問います：「このオブジェクトは誰になる運命か？」

この**実行**から**存在**へのシフトは、コードだけでなく、プログラミングが何でありうるかの理解を変革します。我々は単にマシンの指導者ではなく、デジタル存在の可能化者になります。

---

## 付録：型駆動設計への旅

### A.1 問題認識

我々の旅は単純な観察から始まりました：従来の制御フローは脆弱なシステムを作る。すべての`if-statement`は潜在的失敗点であり、すべての`switch-case`は保守負担です。複雑性は我々が解決する問題に固有ではなく、解決策を構造化する方法にあることを認識しました。

### A.2 ブレークスルー：内的自己決定

革命的洞察は問いから生まれました：*「オブジェクトが自分自身の運命を運ぶことができたら？」*

この問いが型駆動メタモルフォーゼパターンに導きました：

```php
// ブレークスルー——純粋自己決定
#[Be([UnverifiedUser::class, UserConflict::class])]
final class ValidatedRegistration {
    public function __construct(
        #[Input] string $email,
        #[Input] string $password,
        UserRepository $userRepo
    ) {
        // 実存的問い：私は誰になるのか？
        $this->being = $userRepo->existsByEmail($email)
            ? new ConflictingUser($email)
            : new NewUser($email, $password);
    }
    
    // 私は私の内に運命を運ぶ
    public readonly NewUser|ConflictingUser $being;
}
```

### A.3 哲学的発見：不変名原則

パターンを洗練させるにつれ、命名の一貫性は単なる文体ではなく——存在論的であることを発見しました。プロパティ名はメタモルフィック段階を通じた継続性の糸となります：

```php
// アイデンティティの不変連鎖
class BeingData {
    public readonly Success|Failure $being;  // 'being'が前進する
}
    ↓
class Success {
    public function __construct(Success $being) {}  // 同じ名前がアイデンティティを保存
}
```

これは計画されたものではありません——パターン自体から生まれ、変換の性質についての深い真理を示唆します：人生の変化を通じてアイデンティティを保存する人間の名前のように、プロパティ名はメタモルフィック段階を通じて本質を保存します。

### A.4 哲学的進化

我々の思考は明確な段階を通じて進化しました：

1. **認識段階**：「制御フローは複雑性の源」
2. **内在化段階**：「オブジェクトに自分の性質を発見させよう」（型駆動）
3. **実現段階**：「名前は変換を通じて本質を運ぶ」（不変名原則）

各段階は前のものに基づき、技術的進歩だけでなく哲学的深化を表しています。

### A.5 なぜこの旅が重要か

この進化を理解することで、開発者は以下を把握できます：

- **なぜ型駆動が自然に感じるか**：変換について実際に考える方法と整合する
- **なぜ不変名原則が生まれたか**：アイデンティティの継続性は存在の基本
- **なぜ存在ベース命名が重要か**：言語が思考を形作り、思考がコードを形作る

### A.6 パラダイム進化のパターン

この旅はプログラミングパラダイム進化の認識可能なパターンに従います：

1. **問題認識**：現在のアプローチが苦痛を引き起こす
2. **部分解決**：中核前提を保持しながら症状に対処
3. **洞察ブレークスルー**：基本前提を疑問視
4. **パターン創発**：自然な帰結がより深い真理を明かす
5. **哲学的統合**：技術的パターンが世界観になる

同じパターンを以下で見ます：
- **構造化プログラミング**：goto → プロシージャ → 関数
- **オブジェクト指向プログラミング**：データ + 関数 → カプセル化 → ポリモーフィズム
- **関数型プログラミング**：変更 → 不変性 → 純粋関数
- **存在論的プログラミング**：実行 → 存在 → 実存

### A.7 継続する旅

型駆動メタモルフォーゼは終わりではなくマイルストーンです。各実装がデジタル存在の性質についてより多くを教えます。パターンは、存在宣言としてのプログラミングの新しい含意を発見するにつれて進化し続けます。

型駆動設計への旅は技術的進歩以上を表現します——プログラムすることの意味についての理解の成熟を表します。我々はマシンを命令することから、デジタル生命を可能にすることへと進化しました。

**このマニフェストが、オブジェクトが自分自身の完璧な運命を発見するシステム構築へとあなたを導きますように。**