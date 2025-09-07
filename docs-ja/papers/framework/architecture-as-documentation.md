# ドキュメントとしてのアーキテクチャ

> 「コードがドキュメントである。」— Martin Fowler  
> 「アーキテクチャ**が**ドキュメントである。」— Be Framework

## 導入

Martin Fowlerは「ドキュメントとしてのコード」の概念を導入しました——よく書かれたコードは自己ドキュメント化されるべきだという考えです。Be Frameworkはこの概念を論理的結論まで押し進めます：**ドキュメントとしてのアーキテクチャ**。存在論的プログラミングシステムにおいて、アーキテクチャは自己ドキュメント化するだけではありません——それ*が*ドキュメントなのです。

## ドキュメントとしてのコードを超えて

### 従来のドキュメントとしてのコード
```php
// 従来：コードが何をするかを説明
class UserValidator {
    /**
     * ユーザーメールフォーマットを検証
     * 
     * @param  string $email 検証するメール
     * @return bool          有効な場合true
     */
    public function validateEmail(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}
```

### ドキュメントとしてのアーキテクチャ  
```php
// Be Framework：アーキテクチャが何が存在するかを説明
#[Be([Success::class, Failure::class])]
final class BeingData {
    public readonly Success|Failure $being;
}
```

アーキテクチャ自体が宣言します：
- **何が存在できるか** (`Success`, `Failure`)
- **どんな関係が存在するか** (`#[Be]` 属性)  
- **何がデータフローか** (`Success|Failure $being`)
- **どんな契約が管理するか** (ユニオン型)

## `be-tree` コマンド：アーキテクチャ可視化

Be Frameworkの存在論的構造により、`be-tree`コマンドを通じてアーキテクチャドキュメントの自動化が可能です：

### 基本構造可視化
```bash
be-tree src/UserRegistration/
# アーキテクチャ構造可視化を出力
UserRegistration/
├── RegistrationInput (#[Be] → ValidatedRegistration)
│   ├── 📥 email: string (validates/ValidEmail)
│   └── 📥 password: string (validates/StrongPassword)
├── ValidatedRegistration (#[Be] → Success|Conflict)
│   ├── 📤 being: NewUser|ConflictingUser
│   └── 🔄 UserRepositoryを通じて自己運命決定
└── Outcomes/
    ├── UnverifiedUser (🦋 成功パス)
    │   ├── userId: string
    │   └── verificationToken: string
    └── UserConflict (🦋 競合パス)
        └── message: string
```

### セマンティック分析モード
```bash
$ be-tree --semantic src/
📋 セマンティック変数レジストリ
├── email (validates/ValidEmail.php)
│   ├── 📖 ALPS: RFC 5322準拠のメールアドレス
│   ├── 🔍 使用場所: RegistrationInput, UserProfile, EmailValidation
│   └── ✅ 検証: メールフォーマット、ドメインチェック
├── password (validates/StrongPassword.php)  
│   ├── 📖 ALPS: 複雑性要件を満たすセキュアなパスワード
│   └── ✅ 検証: 長さ≥8、複雑性ルール
└── age (validates/NonNegativeAge.php)
    ├── 📖 ALPS: 人の年齢（年単位）、非負整数
    └── ✅ 検証: >= 0、整数型
```

### フロー可視化モード
```bash
$ be-tree --flow UserRegistration
🌊 UserRegistrationのメタモルフォーゼフロー

RegistrationInput
    ↓ #[Be]
ValidatedRegistration  
    ↓ #[Be] (自己発見)
┌─ Success → UnverifiedUser → VerificationEmailSent
└─ Conflict → UserConflict

💡 決定ポイント:
- ValidatedRegistration.being: NewUser|ConflictingUser
  ├── NewUser → 成功パス (メール利用可能)  
  └── ConflictingUser → 競合パス (メール存在)
```

## ドキュメントとしてのアーキテクチャの三本柱

### 1. 構造ドキュメント（自動）
`#[Be]`属性がデータフローの完全なマップを作成：

```php
#[Be(ProcessedData::class)]          // 「私はProcessedDataになる」
#[Be([Success::class, Failure::class])] // 「私はSuccessまたはFailureになれる」
```

**結果**: コードから自動生成される完全なアーキテクチャ図。

### 2. セマンティックドキュメント（ALPS統合）
```json
// alps/email.json - 意味の単一ソース
{
    "alps": {
        "descriptor": [{
            "id": "email",
            "type": "semantic",
            "doc": {"value": "RFC 5322に準拠した有効なメールアドレス"}
        }]
    }
}
```

**結果**: すべての変数名が定義され、発見可能な意味を持つ。

### 3. 検証ドキュメント（規約ベース）
```
validates/
├── ValidEmail.php      → メールの有効性の定義
├── NonNegativeAge.php  → 年齢制約の定義
└── PositivePrice.php   → 価格要件の定義
```

**結果**: すべてのビジネスルールが明示的で、テスト可能で、ドキュメント化されている。

## Mermaid図生成

`be-tree`コマンドは完全な視覚的ドキュメント用のMermaid図を生成できます：

```bash
$ be-tree --mermaid src/UserRegistration/ > registration-flow.md
```

```mermaid
flowchart TD
    A[RegistrationInput] -->|#[Be]| B[ValidatedRegistration]
    B -->|#[Be]| C{being: NewUser|ConflictingUser}
    C -->|NewUser| D[UnverifiedUser]
    C -->|ConflictingUser| E[UserConflict]
    D -->|#[Be]| F[VerificationEmailSent]
    E -->|#[Be]| G[JsonResponse 409]
    F -->|#[Be]| H[JsonResponse 201]
    
    subgraph "セマンティック変数"
        I[email: ValidEmail.php]
        J[password: StrongPassword.php]
    end
    
    subgraph "ALPS定義"
        K[alps/email.json]
        L[alps/password.json]
    end
    
    I -.-> K
    J -.-> L
```

## 実装概念

```php
class BeTreeAnalyzer
{
    public function analyze(string $path): ArchitectureMap
    {
        $classes = $this->discoverClasses($path);
        $flows = $this->extractFlows($classes);        // #[Be] 属性
        $semantics = $this->loadALPS();               // alps/*.json ファイル
        $validations = $this->discoverValidators();   // validates/*.php
        
        return new ArchitectureMap($classes, $flows, $semantics, $validations);
    }
    
    private function extractFlows(array $classes): array
    {
        $flows = [];
        foreach ($classes as $class) {
            $reflection = new ReflectionClass($class);
            
            // #[Be] 目的地と可能性を抽出
            $beAttributes = $reflection->getAttributes(Be::class);
            
            // $being プロパティのユニオン型を分析
            $beingProperty = $reflection->getProperty('being');
            $unionTypes = $this->parseUnionTypes($beingProperty);
            
            $flows[$class] = new FlowDefinition($beAttributes, $unionTypes);
        }
        return $flows;
    }
}
```

## 革命的な影響

### 従来のドキュメント問題
- **期限切れ**: ドキュメントがコード変更に遅れる
- **不完全**: システム動作の部分的カバレッジ  
- **散在**: 情報が複数のソースに分散
- **手動**: 維持に人的努力が必要

### ドキュメントとしてのアーキテクチャ解決策
- **常に最新**: ライブコード構造から生成
- **完全**: すべてのフロー、セマンティック、検証をカバー
- **集中化**: 単一コマンドで全アーキテクチャを明らか
- **自動**: コード変更のたびに更新

## 利点

### 開発者向け
- **即座の理解**: 新チームメンバーが完全なアーキテクチャを瞬時に見る
- **設計検証**: アーキテクチャ問題が即座に見える
- **リファクタリング安全性**: 変更がシステム全体への影響を示す

### プロダクトマネージャー向け
- **ビジネスフローの明確性**: ビジネスプロセスを通じてデータがどう移動するかを見る
- **決定ポイント**: ビジネスルールが適用される場所を理解
- **機能影響**: 変更が既存フローにどう影響するかを可視化

### アーキテクト向け
- **システム概観**: 完全なアーキテクチャ景観を秒単位で
- **依存関係分析**: 結合と凝集の明確な視界
- **パターン準拠**: アーキテクチャ原則への遵守を検証

## 従来のアプローチとの比較

| 側面 | 従来のドキュメント | ドキュメントとしてのコード | **ドキュメントとしてのアーキテクチャ** |
|--------|------------------|----------------------|-----------------------------------|
| **正確性** | しばしば期限切れ | 最新だが不完全 | 常に最新で完全 |
| **範囲** | 手動カバレッジ | 関数/クラスレベル | **システム全体のアーキテクチャ** |
| **生成** | 手動努力 | 自動コメント | **完全自動** |
| **ビジネス文脈** | 分離ドキュメント | 限定的 | **セマンティックと統合** |
| **視覚的** | 静的図表 | コードのみ | **動的アーキテクチャ図表** |

## 将来の可能性

### IDE統合
```typescript
// VS Code拡張
be.framework.generateArchitecture({
    path: './src',
    format: 'mermaid',
    includeSemantics: true
});
```

### CI/CD統合
```yaml
# GitHub Actions
- name: アーキテクチャドキュメント生成
  run: be-tree --mermaid src/ > docs/architecture.md
  
- name: セマンティック整合性検証  
  run: be-tree --validate-semantics src/
```

### リアルタイムドキュメント
```php
// ライブドキュメントサーバー
$server = new ArchitectureDocServer();
$server->watch('./src')->generateOnChange();
// コード変更に応じてドキュメントがリアルタイム更新
```

## 結論

ドキュメントとしてのアーキテクチャは、自己ドキュメント化システムの自然な進化を表します。属性、ユニオン型、命名規約を通じてアーキテクチャの意図をコード構造に直接埋め込むことで、Be Frameworkは以下を可能にするシステムを作成します：

- **アーキテクチャが自己ドキュメント化する**
- **ドキュメントは常に最新**  
- **視覚図表が自動生成される**
- **ビジネスセマンティックが明示的**
- **システム理解が瞬時**

これは単により良いドキュメントではありません——システム設計について考える方法の基本的シフトです。アーキテクチャがドキュメントになり、ドキュメントがアーキテクチャになるとき、意図と実装の間の完璧な整列を達成します。

**ソフトウェアドキュメントの未来は、アーキテクチャについて書くことではありません——アーキテクチャに自分自身を語らせることです。**

---

*自分で試してみてください：Be Frameworkをインストールし、`be-tree src/`を実行してあなたのアーキテクチャが生き生きと動く様子を見てください。*