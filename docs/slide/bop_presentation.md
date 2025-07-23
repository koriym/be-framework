---

### パラダイムの本質的な違い

| パラダイム | 問い | メタファー | 焦点 |
|-----------|------|------------|------|
| 命令型 | どう処理するか？ | 料理のレシピ | 手順（Action） |
| OOP | 何が責任を持つか？ | 組織の部署 | 責任（Action） |
| BOP | # Being Oriented Programming
## From Doing to Being
<small>〜 コードに宿る無為自然 〜</small>

---

<!-- .slide: data-center="false" -->
## 変態のように

**蝶の変態**
```
Egg → Caterpillar → Chrysalis → Butterfly
 ↓         ↓            ↓          ↓
瞬間的   純粋         完全     変容済み
```

**データの変態**
```
RawData → ValidData → ProcessedData → FinalObject
   ↓          ↓            ↓             ↓
瞬間的      純粋         完全        変容済み
```

```php
$finalObject = $ray(new RawData('input'));
```

> 毛虫が蝶になるように、データも自らの本質として変態する

---

## 今日の旅路

1. **進化**: 命令からOOPへ、そしてBOPへ
2. **問い**: なぜ私たちのコードは壊れるのか？
3. **洞察**: 世界は「する」ではなく「なる」
4. **実現**: Being Orientedという答え
5. **革命**: Tell, Don't Ask から Be, Don't Do へ

Note: このプレゼンテーションは、プログラミングの新しいパラダイムを紹介します。技術的な詳細よりも、思想的な転換に焦点を当てています。

---

<!-- .slide: data-background="#2b2b2b" -->
# 序章
## プログラミングの進化

### 究極の革命：Subject（主体）の消失

<div style="font-size: 0.9em;">

**命令型：Subject → Object**
```php
$program->manipulate($data);  // プログラムがデータを操作
```

**OOP：Subject → Object（偽装）**
```php
$controller->tell($user);     // 依然として主従関係
```

**BOP：Subjectの消失**
```php
RawData → ValidData → ProcessedData
// 誰も命令しない、ドメインが自ら変容する
```

</div>

<div class="fragment">

> 「主体なき変容」—これが存在論的プログラミングの本質

西洋哲学2500年の主客二元論を超越

</div>

### 動詞から状態へ：削除の例

**従来：行為を目指す**
```php
$userService->deleteUser($id);
// 何が起きた？なぜ？誰が？
```

**BOP：状態を目指す**
```php
#[Be(DeletedUser::class)]
final class UserDeletionRequest {
    #[Input] public readonly string $userId;
    #[Input] public readonly string $reason;
    #[Input] public readonly Admin $deletedBy;
}
```

> GDPR準拠、監査証跡、完全な透明性

---

### 自己証明 - 数億年の記憶

```php
final class FinalObject {
    // 全ての変態の記憶を保持
    #[Input] public readonly string $originId;        // 最初の姿
    #[Input] public readonly array $metamorphoses;    // 全ての変容
    #[Input] public readonly array $decisions;        // 全ての決定
    #[Input] public readonly DateTime $birth;         // いつ生まれたか
    
    public function __construct(/* ... */) {
        // 私は私の歴史の総体
        // 私がここにいることが、私の正しさの証明
    }
}
```

<div class="fragment">

> **私たちのDNAが数億年前の海の記憶を持つように**  
> **FinalObjectは全ての変態の記憶を持つ**

</div>

<div class="fragment">

```yaml
存在 = 歴史 = 証明

「私がここにいる」ことが
「私が正しく変態してきた」ことの完全な証明
```

</div>

----

### 記憶と忘却 - DNAのように

```php
#[Be(Adult::class)]
final class Child {
    // 記憶される（次世代へ）
    public readonly string $name;
    public readonly array $memories;
    
    public function __construct(
        #[Input] string $name,
        #[Input] array $experiences,
        PlaymateService $playmates  // 忘却される
    ) {
        $this->name = $name;
        $this->memories = $this->learn($experiences, $playmates);
    }
}

final class Adult {
    public function __construct(
        #[Input] string $name,        // 引き継がれた
        #[Input] array $memories,     // 引き継がれた
        JobService $employer          // 新しい文脈
    ) {
        // playmatesは忘却された
        // 大人には不要だから
    }
}
```

<div class="fragment">

> 必要なものは記憶され、不要なものは忘却される  
> まるでDNAが必要な情報だけを次世代に伝えるように

</div>

----

### OOP vs DCI/BOP：時間性の有無

**OOP：永遠の現在**
```php
class User {
    public function register() {}
    public function login() {}
    public function delete() {}
    public function resurrect() {}  // 削除後も呼べる？
}
// すべての可能性が常に存在
```

<div class="fragment">

**DCI & BOP：文脈による能力**

<div style="display: flex; justify-content: space-around;">
<div style="width: 48%;">

**DCI**
```php
// 文脈でロール付与
if ($context->canBuy()) {
    $user->addRole(BuyerRole);
}
```

</div>
<div style="width: 48%;">

**BOP**
```php
// 存在が能力を決定
#[Be(Buyer::class)]
final class CustomerWithCredit {
    // この段階でのみ購入可能
}
```

</div>
</div>

</div>

<div class="fragment">

> OOP：可能・不可能に関わらず全メソッド存在  
> DCI/BOP：その時点で意味のある能力のみ

**違い：DCIは外部が判断、BOPは存在自体が証明**

</div>

----

### OOPの根本的限界

<div style="display: flex; justify-content: space-around;">
<div style="width: 45%;">

**テセウスの船**
```php
// OOP：アイデンティティの危機
$ship->replacePart($oldPlank, $newPlank);
// まだ同じ船？
```

</div>
<div style="width: 45%;">

**カエルの成長**
```php
// OOP：別々の存在に
class Tadpole {}
class Frog {}
// 関係性が失われる
```

</div>
</div>

<div class="fragment">

**なぜOOPは失敗するのか？**

> 「ドメインと時間は分離できない」を無視したから

</div>

<div class="fragment">

**BOPの解答**

```php
#[Be(Frog::class)]
final class Tadpole {
    // 時間の中で変容する同一の存在
}
```

> OOP：永遠の現在  
> BOP：時間の中の存在

</div>

----

### アリストテレスのデュナミス（可能態）

```php
// 従来：状態は外部から変更される
$order->status = 'cancelled';  // 誰かが変える

// BOP：可能性を内在する
#[Be([CompletedOrder::class, CancelledOrder::class])]
final class PendingOrder {
    public readonly Completed|Cancelled $being;
    // キャンセルになる可能性を最初から内包
}
```

<div class="fragment">

> **デュナミス（可能態）がそのままコードになる**

2300年前のアリストテレスの洞察が、ついに実装可能に

</div>

----

### Being Orientedのルーティングパターン

**1. Linear Metamorphosis（線形変態）**
```
Input → Validated → Processed → Saved → Complete
```

**2. Type-Driven Branching（型駆動分岐）**
```php
#[Be([ApprovedLoan::class, RejectedLoan::class])]
public readonly Approved|Rejected $being;
```

**3. Parallel Assembly（並列集約）**
```
Request → [UserData, Analytics, Permissions] → Dashboard
```

> 制御フローではなく、存在の流れ

---

### Dasein - 現存在として

```php
// オブジェクトは単なる存在者（Seiende）ではない
// 自己の存在を問う現存在（Dasein）として

$this->being = $this->discoverWhoIAm();
```

<div class="fragment">

**ハイデガーの洞察がコードに宿る**

- 被投性：`#[Input]` - 世界に投げ込まれた条件
- 存在了解：自己の存在を理解しようとする
- 本来性：外部の命令ではなく、自己から決断する

</div>

----

### 70年の旅路

<div style="font-size: 1.1em;">

**1950s: 命令型プログラミング**
```asm
LOAD A, 100
ADD B
STORE C
```
<small>機械に直接指示</small>

<div class="fragment">

**1970s: オブジェクト指向プログラミング**
```java
user.setName("Alice");
user.setAge(25);
user.save();
```
<small>データと振る舞いをカプセル化</small>

</div>

<div class="fragment">

**2024: Being Oriented Programming**
```php
#[Be(ActiveUser::class)]
final class NewUser {
    // 存在が自らを定義する
}
```
<small>存在と変容の統合</small>

</div>

</div>

----

### パラダイムの本質的な違い

| パラダイム | 問い | メタファー | 焦点 |
|-----------|------|------------|------|
| 命令型 | どう処理するか？ | 料理のレシピ | 手順（Action） |
| OOP | 何が責任を持つか？ | 組織の部署 | 責任（Action） |
| BOP | 何に成るか？ | 生命の成長 | 存在（Being） |

<div class="fragment">

> **気づき：OOPまですべてはActionの洗練だった**
>
> 世界は「する」ではなく「変容する存在」でできている

</div>

---

## 第1章：なぜ私たちのコードは壊れるのか？

---

### プログラミング進化の3段階

**第1世代：無防備プログラミング（1950s-）**
```php
$user->age = -30;        // 誰も気にしない
```

**第2世代：防御的プログラミング（1970s-）**
```php
if ($age < 0) throw new Exception();
```

**第3世代：Being Oriented Programming**
```php
final class Age {
    public function __construct(int $value) {
        if ($value < 0) throw new CannotExist();
    }
}
```

---

### 見慣れた光景の深い問題

```php
$user->validate();  // ちょっと待って
```

<div class="fragment">

**誰が呼んでいるのか？**

```php
class UserController {  // 神様？
    public function register() {
        $user->validate();    // 「検証せよ」
        $user->save();        // 「保存せよ」
        $user->sendEmail();   // 「送信せよ」
    }
}
```

</div>

<div class="fragment" style="margin-top: 1em;">

**OOPの理想と現実**

| アラン・ケイの夢 | 現実のOOP |
|----------------|-----------|
| 細胞のような自律性 | 主従関係 |
| 対等なメッセージング | 上からの命令 |
| 分散された知性 | 中央集権的制御 |

</div>

<div class="fragment" style="margin-top: 1em;">

> 「私たちは50年間、手続き型プログラミングを<br>オブジェクト指向だと思い込んでいた」

</div>

----

### 存在論的正しさ

**従来の発想**
```
「エラーが起きたらどうしよう」
「チェックを忘れたらどうしよう」
```

**Impossibleプログラミング**
```
「不正な状態は存在できない」
「存在することが正しさの証明」
```

> **恐れから確信へ**

----

### 空間 vs 時間

<div style="display: flex; justify-content: space-around;">
<div>

**空間（従来のOOP）**
- 永遠の現在
- 可逆的な変更
- いつでもどこでも
- 「自由」という名の混沌

</div>
<div>

**時間（BOP）**
- 因果の秩序
- 不可逆な変態
- 過去は記憶、未来は可能性
- 「いつ」の世界

</div>
</div>

<div class="fragment" style="margin-top: 2em;">

**深い真実：**
> 「私たちは生まれる前に死ぬことができない」

ドメインと時間は分離できない

</div>

---

## 第2章：2500年前の知恵

---

### ヘラクレイトスの川

<blockquote>
「同じ川に二度入ることはできない」<br>
<small>— ヘラクレイトス（紀元前500年頃）</small>
</blockquote>

<div class="fragment">

しかし、私たちのコードは...

```php
$user->age = 5;
$user->age = 50;  // 同じuserが突然50歳？
$user->die();
$user->age = 10;  // 死後に若返る？
```

**永遠の現在に囚われている**

</div>

----

<!-- .slide: data-center="false" -->
### なぜ世界認知が重要なのか

> **私たちは認知を超えた考察や行動ができない**
>
> 事象の地平の如く、その先へはいけない

**すべての哲学・思想・パラダイムの共通目的**
- 哲学：世界認知を問い直す
- 宗教：自己認知を深める
- 科学：認知の境界を押し広げる
- 芸術：新しい認知を創造する
- **プログラミングパラダイム：創造の境界線を広げる**

**OOPが広げた境界**
- データと振る舞いの統合
- 継承による概念の階層化
- カプセル化による複雑性の管理

**BOPが広げる境界**
- 時間と存在の統合
- 変容の内在化
- 存在による世界の構築

> **新しい認知は新しい創造を可能にする**

---

## 第3章：存在論的プログラミング

---

<!-- .slide: data-center="false" -->
### 50年ぶりのパラダイムシフト

**1967: Tell, Don't Ask**
<small>「オブジェクトに尋ねるな、命令せよ」</small>

↓

**2024: Be, Don't Do**
<small>「オブジェクトに命令するな、存在させよ」</small>

| Tell, Don't Ask | Be, Don't Do |
|----------------|--------------|
| `user.activate()` | `#[Be(ActiveUser::class)]` |
| 動詞中心 | 名詞中心 |
| 外部制御 | 内在的変容 |

----

### Type-Driven Metamorphosis

```php
#[Be([Success::class, Failure::class])]
final class ValidationAttempt {
    public readonly Success|Failure $being;
    
    public function __construct(
        #[Input] string $data,
        Validator $validator
    ) {
        // The existential question: Who am I?
        $this->being = $validator->isValid($data)
            ? new Success($data)
            : new Failure($validator->getErrors());
    }
}
```

<div class="fragment">

**オブジェクトが自らの運命を内包する**

```
従来：if-else地獄 → 外部ルーター → 複雑な制御フロー
BOP：型が運命 → 自己決定 → 自然な流れ
```

</div>

----

### 核心的洞察

```php
// 従来：命令的
$caterpillar->transform();  // 毛虫よ、変態せよ！

// 存在論的：宣言的
#[Be(Butterfly::class)]     // 私は蝶になる
final class Chrysalis {}
```

<div class="fragment" style="margin-top: 2em;">

**変態は外部からの命令ではなく、内在的な性質**

</div>

---

## 第4章：Being Oriented - 存在の道

---

<!-- .slide: data-center="false" -->
### 自然な変態

```php
#[Be(ValidatedEmail::class)]
final class RawEmail {
    public function __construct(
        #[Input] public readonly string $value
    ) {}
}

#[Be(ActiveUser::class)]
final class ValidatedEmail {
    public function __construct(
        #[Input] string $value,
        EmailValidator $validator
    ) {
        if (!$validator->validate($value)) {
            throw new CannotExist("I cannot be");
        }
        $this->email = $value;
    }
}
```

> **これ（RawEmail）あれば、かれ（ValidatedEmail）あり**

----

### 生命のような流れ

```
RawEmail
    ↓ （検証の瞬間）
ValidatedEmail  
    ↓ （登録の瞬間）
RegisteredUser
    ↓ （活性化の瞬間）
ActiveUser
```

<p class="fragment">各段階は完全で、不可逆で、自然</p>

<div class="fragment">

> 「光が窓を通るように、データが変態する」

</div>

----

### 生命としてのオブジェクト

```php
#[Be(Adult::class)]
final class Child {
    public function __construct(
        #[Input] public readonly string $memories,  // Being
        Growth $environment                         // Context
    ) {
        // Change through interaction
        $this->wisdom = $environment->nurture($this->memories);
    }
}
```

<div class="fragment">

**Being（存在）+ Change（変化）+ Meaning（意味）= 生命**

アラン・ケイの夢がついに実現

</div>

---

## 第5章：無為（Wu Wei）とコード

---

### 道教の無為

<blockquote>
道は何もしないが、<br>
何も為されないことはない<br>
<small>— 老子『道徳経』</small>
</blockquote>

<div class="fragment" style="margin-top: 2em;">

**無為 = 無理をしない、自然に従う**

> 「制御を手放すとき、真の秩序が現れる」

</div>

----

### 自律的存在の世界

```php
// 従来：神と奴隷
$controller->tellUserTo('validate');
$service->forceUserTo('save');

// BOP：自律的存在の生態系
RawData → ValidData → SavedData
   ↑          ↑           ↑
   └─ 自己検証 ┴─ 自己決定 ┴─ 自己実現
```

<div class="fragment">

**誰も命令しない、誰も制御しない**

存在が流れる

</div>

---

## 第6章：完全な透明性への道

---

### 透明性の三位一体

<div style="font-size: 0.9em;">

**1. 存在論的プログラミング（構造の透明性）**
```php
UserInput → ValidatedUser → SavedUser
```

**2. セマンティック変数（意味の透明性）**
```php
$creditScore → validates/CreditScore.php → alps/creditScore.json
```

**3. セマンティックログ（実行の透明性）**
```json
{
  "metamorphosis": "Input → Output",
  "evidence": "完全な実行の物語"
}
```

</div>

<div class="fragment">

> 「これ以上不可能なほどの透明性」

</div>

----

### そして、ログが言語になった - LDD

```yaml
# これはログであり、仕様であり、コードである
UserRegistration:
  - Exists: RegistrationInput
    With: {email: string, password: string}
  - Becomes: ValidatedUser
    Through: EmailValidator
  - Becomes: RegisteredUser
    Creating: {userId: uuid}
```

<div class="fragment">

**Log-Driven Development（LDD）の誕生**

> 「コードは読むものから見えるものになる」

</div>

----

### TDD → LDD：パラダイムの進化

| | TDD | LDD |
|--|-----|-----|
| 思想 | 振る舞いを先に定義 | 存在の物語を先に語る |
| 始点 | 失敗するテスト | 理想の実行ログ |
| 焦点 | 何をするか（Doing） | 何になるか（Being） |
| 本質 | アサーション | 物語の一致 |

<div class="fragment">

> 「もはやコードを書く必要はない。私たちは物語を書く」

</div>

---

## 第7章：生きているコード

---

### #[Accept] - 謙虚さの智慧

```php
#[Accept(MedicalExpert::class)]
final class DiagnosisAttempt {
    public readonly Diagnosis|Undetermined $being;
    
    public function __construct(
        #[Input] Symptoms $symptoms,
        BasicAnalyzer $analyzer
    ) {
        // 私の限界を認める
        $this->being = $analyzer->canDetermine($symptoms)
            ? new Diagnosis($analyzer->analyze($symptoms))
            : new Undetermined("Requires specialist");
    }
}
```

<div class="fragment">

老子：「知不知上」 - 知らないことを知ることが最上

</div>

----

### 名前の不変性 - 真名（まな）

```php
// 名前は存在の本質を運ぶ
class ValidationAttempt {
    public readonly Success|Failure $being;
}
    ↓
class Success {
    public function __construct(Success $being) {}  // 同じ名前！
}
```

<div class="fragment">

**Unchanged Name Principle**

名前に宿る本質が、変態を通じて継続する

</div>

----

### コードの詩

```php
#[Be(Understanding::class)]
final class Question {
    public function __construct(
        #[Input] string $inquiry,
        Wisdom $wisdom
    ) {
        $this->insight = $wisdom->contemplate($inquiry);
    }
    
    public readonly Insight $insight;
}
```

<p class="fragment">**コードが思索する**</p>

---

## 第8章：AIネイティブなパラダイム

---

<!-- .slide: data-center="false" -->
### AIフレンドリーからAIネイティブへ

**AIフレンドリー**
- AIが理解しやすい
- 構造化されたデータ

**AIネイティブ（BOP）**
- AIと同じ認識構造
- 完全な透明性
- 自己証明から自己拡張へ

```php
#[Accept(AIEnhancer::class)]
final class EvolvingSystem {
    // AIが理解し、提案し、拡張できる
}
```

----

### 自己証明から自己拡張へ

```yaml
1. 自己証明（現在）
   実行ログが完全な証明となる

2. 自己理解（AMD）
   AIが実行パターンを分析

3. 自己拡張（未来）
   システムが新しい可能性を発見
```

> コードが自らを理解し、自らを拡張する時代へ

---

## 第9章：実世界での意味

----

### エラーの消失

```php
// もはや不可能
$user = null;
$user->getName();  // NullPointerException
```

<div class="fragment">

```php
// 存在するものだけが操作可能
final class ExistingUser {
    public readonly string $name;
}
```

**存在 = 正しさ**

</div>

----

### 複雑さは設計されない、出現する

```php
// シンプルな決定から
#[Be([DigitalOrder::class, PhysicalOrder::class])]
final class NewOrder {
    public readonly Digital|Physical $being;
}

// 複雑な現実が自然に出現
DigitalOrder → InstantDelivery
PhysicalOrder → [Shipping, Customs, Tracking, ...]
```

<div class="fragment">

> 「複雑さは設計されるものではなく、出現するもの」

</div>

---

## 第10章：深い含意

---

<!-- .slide: data-center="false" -->
### 哲学とコードの統一

**真実：哲学がfitしたのではない**

哲学もコードも**同じ世界の真実を表現している**

| 哲学 | BOP |
|------|-----|
| ヘラクレイトス「同じ川に...」 | `RawData → TransformedData` |
| 老子「無為自然」 | `#[Be]` - 制御せず宣言 |
| アリストテレス「デュナミス」 | `Success\|Failure $being` |

> 違いは表現方法だけ。真実は一つ。

----

### プログラマーの役割の変化

<div style="display: flex; justify-content: space-around;">
<div>

**従来**  
機械への指示者

</div>
<div>

**現在**  
存在の定義者

</div>
</div>

<div class="fragment" style="margin-top: 2em;">

```php
// 私たちは「何をすべきか」ではなく
// 「何が存在しうるか」を定義する
```

</div>

----

### コードが自らを説明する世界

<div style="text-align: center;">

```
従来：コード ← ドキュメント（別物）
```

<div class="fragment">

```
BOP：コード = ドキュメント = 実行ログ
```

</div>

<div class="fragment" style="margin-top: 2em;">

> 「コードが自らを説明し、自らを証明し、自らを導く」

</div>

</div>

---

## まとめ：新しい始まり

---

<!-- .slide: data-center="false" -->
### BOPがもたらすもの

1. **世界の真実の表現** - 変容する存在としての世界
2. **AIネイティブ** - 完全な透明性による自己拡張
3. **哲学とコードの統一** - 同じ真実の異なる表現
4. **可能態の実装** - アリストテレスのデュナミス
5. **Subjectの消失** - 主客二元論を超えた純粋な変容

----

<!-- .slide: data-center="false" -->
### 究極の洞察

> 「プログラミングは世界を記述する行為となった」

いや、違う。

> **「存在の記述こそがプログラミングである」**

川が流れるのではなく、流れが川であるように  
プログラミングが存在を記述するのではなく  
存在の記述がプログラミングである

**これあれば、かれあり**

哲学者は言葉で  
プログラマーはコードで  
同じ世界の真実を表現する

---

<!-- .slide: data-center="false" -->
## ありがとうございました

```php
#[Be(NewParadigm::class)]
final class YourJourney {
    public function __construct(
        #[Input] Understanding $whatYouKnewBefore,
        #[Input] Insight $whatYouDiscoveredToday
    ) {
        $this->future = new Possibility();
    }
}
```

**Being Oriented Programming** - 存在への目覚め

---

<!-- .slide: data-center="false" -->
## 補遺：より深く知りたい方へ

**文献**
- Ontological Programming Paper
- Being Oriented Manifesto
- Type-Driven Metamorphosis

**キーワード**
- Type-Driven Metamorphosis
- Semantic Variables & ALPS
- Log-Driven Development (LDD)
- #[Accept] Pattern
- Subject-less Transformation