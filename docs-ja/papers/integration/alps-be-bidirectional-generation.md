# ALPSとBe Framework：双方向生成

> 「設計を理解する最良の方法は実装することである。設計を実装する最良の方法は完全に理解することである。」— 匿名

## 導入

Be Frameworkは、ALPS（Application-Level Profile Semantics）哲学と実行可能コードのユニークな融合を表現します。この文書では、ALPS仕様とBe Framework実装間の双方向関係を探求し、セマンティック整合性を維持しながら、いかに各々が他を生成できるかを実証します。

## Be Frameworkの二重性質

Be Frameworkは同時に以下として存在します：
- 具体的ビジネスロジックを持つ**実行可能実装**
- セマンティック状態遷移を持つ**生きているALPS仕様**
- REST、GraphQL、メッセージキューとして現れることができる**プロトコル非依存設計**

この二重性は、仕様と実装間の完璧な一貫性を維持する双方向生成プロセスを可能にします。

## ALPSからBe Framework生成

### ソースALPS仕様
```json
{
  "alps": {
    "title": "注文処理ワークフロー",
    "doc": { "value": "Eコマース注文処理状態遷移" },
    "descriptor": [
      {
        "id": "OrderRequest",
        "type": "semantic",
        "doc": { "value": "顧客からの初期注文リクエスト" },
        "rt": ["ValidOrder", "InvalidOrder"]
      },
      {
        "id": "ValidOrder",
        "type": "semantic", 
        "doc": { "value": "検証を通過した注文" },
        "rt": ["ProcessedOrder", "CancelledOrder"]
      },
      {
        "id": "ProcessedOrder",
        "type": "semantic",
        "doc": { "value": "正常に処理された注文" },
        "rt": ["ShippedOrder", "RefundedOrder"]
      }
    ]
  }
}
```

### 生成されたBe Framework実装
```bash
be-generate --from-alps order-workflow.alps.json --output src/OrderWorkflow/
# src/OrderWorkflow/に完全な実装スケルトンを生成
```

```php
// ALPS仕様から生成
namespace OrderWorkflow;

use Be\Framework\Attribute\Be;
use Be\Framework\Attribute\Input;

#[Be([ValidOrder::class, InvalidOrder::class])]
final class OrderRequest
{
    public readonly Valid|Invalid $being;

    public function __construct(
        #[Input] array $orderData,
        OrderValidator $validator,
        InventoryChecker $inventory
    ) {
        // ALPS遷移に基づく生成構造
        // ビジネスロジック実装が必要
        $this->being = $validator->isValid($orderData)
            ? new Valid($orderData, $inventory)
            : new Invalid($validator->getErrors());
    }
}
#[Be([ProcessedOrder::class, CancelledOrder::class])]
final class ValidOrder
{
    public readonly Processed|Cancelled $being;
    
    public function __construct(
        Valid $validOrderData,
        PaymentProcessor $paymentProcessor,
        OrderRepository $orderRepository
    ) {
        // 実装はALPS状態遷移ルールに従う
        // 具体的ビジネスロジックは実装が必要
    }
}

#[Be([ShippedOrder::class, RefundedOrder::class])]
final class ProcessedOrder
{
    public readonly Shipped|Refunded $being;
    
    public function __construct(
        Processed $processedOrder,
        ShippingService $shippingService,
        RefundProcessor $refundProcessor
    ) {
        // ALPSで定義された最終状態遷移
    }
}
```

### 主要生成機能

**構造生成:**
- クラス階層がALPSディスクリプタ関係を反映
- `#[Be]`属性が`rt`（関係型）値を反映
- ユニオン型が可能な状態遷移に対応

**セマンティック保存:**
- ALPSドキュメントがコードコメントになる
- ディスクリプタIDがクラス名になる
- 状態遷移ロジックは足場が提供されるが実装が必要

**ビジネスロジック足場:**
- コンストラクタパラメータが必要依存関係を示唆
- 状態決定ロジック構造が提供される
- 実装詳細は開発者仕様のまま

## Be FrameworkからALPS生成

### ソースBe Framework実装
```php
namespace LoanProcessing;

#[Be([ApprovedLoan::class, RejectedLoan::class, PendingReview::class])]
final class LoanApplication
{
    public readonly ApprovedLoan|RejectedLoan|PendingReview $being;
    
    public function __construct(
        #[Input] string $applicantId,
        #[Input] float $requestedAmount,
        #[Input] int $creditScore,
        LoanProcessor $processor,
        CreditAnalyzer $analyzer
    ) {
        $analysis = $analyzer->analyze($applicantId, $creditScore);
        
        $this->being = match (true) {
            $analysis->isHighRisk() => new Rejected($analysis->getReason()),
            $analysis->requiresReview() => new PendingReview($analysis->getReviewNotes()),
            default => new Approved($requestedAmount, $analysis->getTerms())
        };
    }
}

#[Be([FinalizedLoan::class, LoanCancellation::class])]
final class ApprovedLoan
{
    public readonly Finalized|Cancelled $being;
    
    public function __construct(
        Approved $approvedLoan,
        DocumentService $documentService,
        CustomerNotificationService $notificationService
    ) {
        // ローン確定のための実装ロジック
    }
}
```

### 生成されたALPS仕様
```bash
$ be-extract --to-alps src/LoanProcessing/ --output loan-processing.alps.json
```

```json
{
  "alps": {
    "title": "ローン処理API",
    "doc": { "value": "Be Framework実装から生成" },
    "version": "1.0",
    "descriptor": [
      {
        "id": "LoanApplication",
        "type": "semantic",
        "doc": { "value": "ローン申請処理ワークフロー" },
        "descriptor": [
          {
            "id": "applicantId",
            "type": "semantic",
            "doc": { "value": "ローン申請者の一意識別子" }
          },
          {
            "id": "requestedAmount", 
            "type": "semantic",
            "doc": { "value": "申請者が要求するローン金額" }
          },
          {
            "id": "creditScore",
            "type": "semantic", 
            "doc": { "value": "申請者の信用スコア" }
          }
        ],
        "rt": ["ApprovedLoan", "RejectedLoan", "PendingReview"]
      },
      {
        "id": "ApprovedLoan",
        "type": "semantic",
        "doc": { "value": "処理のために承認されたローン" },
        "rt": ["FinalizedLoan", "LoanCancellation"]
      },
      {
        "id": "RejectedLoan",
        "type": "semantic",
        "doc": { "value": "拒否されたローン申請" },
        "rt": []
      },
      {
        "id": "PendingReview",
        "type": "semantic", 
        "doc": { "value": "手動レビューが必要なローン" },
        "rt": ["ApprovedLoan", "RejectedLoan"]
      }
    ]
  }
}
```

### 抽出プロセス

**状態遷移分析:**
- `#[Be]`属性が`rt`配列になる
- `$being`プロパティのユニオン型が可能な遷移を定義
- クラス関係がALPSディスクリプタ階層を形成

**セマンティック発見:**
- `#[Input]`付きコンストラクタパラメータがALPSディスクリプタになる
- 変数名がセマンティック定義にリンク（セマンティック変数名）
- クラスとプロパティドキュメントがALPSドキュメントになる

**検証統合:**
- `validates/`フォルダ内容がALPS制約を通知
- ビジネスルールがセマンティックドキュメントになる
- 型情報がALPS型定義を強化

## ALPSからのプロトコル生成

### RESTful API生成
```bash
$ be-extract --to-openapi src/LoanProcessing/ --output loan-api.openapi.json
```

```yaml
openapi: 3.0.0
info:
  title: ローン処理API
  version: 1.0.0
paths:
  /loan-applications:
    post:
      summary: ローン申請を提出
      requestBody:
        content:
          application/json:
            schema:
              type: object
              properties:
                applicantId:
                  type: string
                requestedAmount:
                  type: number
                creditScore:
                  type: integer
      responses:
        '200':
          description: 申請処理済み
          content:
            application/json:
              schema:
                oneOf:
                  - $ref: '#/components/schemas/ApprovedLoan'
                  - $ref: '#/components/schemas/RejectedLoan'
                  - $ref: '#/components/schemas/PendingReview'
```

### GraphQLスキーマ生成
```bash
$ be-extract --to-graphql src/LoanProcessing/ --output loan-schema.graphql
```

```graphql
type LoanApplication {
  applicantId: String!
  requestedAmount: Float!
  creditScore: Int!
  being: LoanApplicationResult!
}

union LoanApplicationResult = ApprovedLoan | RejectedLoan | PendingReview

type ApprovedLoan {
  amount: Float!
  terms: LoanTerms!
  being: ApprovedLoanResult!
}

union ApprovedLoanResult = FinalizedLoan | LoanCancellation
```

## 仕様駆動開発

### 設計ファーストワークフロー
1. **ビジネス分析:** ALPSでワークフローを定義
2. **コード生成:** Be Frameworkスケルトンを生成
3. **実装:** 生成された構造にビジネスロジックを追加
4. **検証:** 実装がALPS仕様と一致することを確認
5. **API生成:** プロトコル固有APIを自動生成

### 実装ファーストワークフロー  
1. **迅速開発:** Be Frameworkで直接実装
2. **仕様抽出:** 実装からALPSを生成
3. **ドキュメント:** ALPSを生きているAPIドキュメントとして使用
4. **クライアント生成:** ALPSからクライアントSDKを生成
5. **契約テスト:** 抽出仕様に対する実装の検証

## ツール統合

### CLIコマンド
```bash
# 双方向生成
be-generate --from-alps workflow.alps.json --output src/
be-extract --to-alps src/ --output generated.alps.json

# プロトコル生成
be-extract --to-openapi src/ --output api.openapi.json
be-extract --to-graphql src/ --output schema.graphql
be-extract --to-grpc src/ --output service.proto

# 検証とテスト
be-validate --alps-compliance src/ workflow.alps.json
be-test --contract-testing src/ api-tests/
```

### IDE統合
```typescript
// VS Code拡張
be.generateFromALPS({
  source: './design.alps.json',
  target: './src/generated/',
  namespace: 'App\\Workflow'
});

be.extractToALPS({
  source: './src/workflow/',
  target: './docs/specification.alps.json',
  includeDocumentation: true
});
```

## 品質保証

### 仕様準拠
```bash
$ be-validate --alps-compliance src/OrderWorkflow/ order-workflow.alps.json
✓ すべての状態遷移がALPS仕様と一致
✓ すべてのセマンティックディスクリプタが実装済み
✓ 孤立状態は検出されず
✓ ビジネスロジックがセマンティック契約を保持
```

### ラウンドトリップ検証
```bash
# 双方向生成整合性をテスト
$ be-extract --to-alps src/ --output extracted.alps.json
$ be-generate --from-alps extracted.alps.json --output src-regenerated/
$ diff -r src/ src-regenerated/
# 構造変更ではなく、ビジネスロジックの差異のみを表示すべき
```

## 双方向生成の利点

### アーキテクト向け
- **設計検証:** ALPS仕様が実装・テスト可能
- **実装確認:** コードが自動的に仕様を生成
- **プロトコル非依存:** 同じ設計がREST、GraphQL、イベントで動作

### 開発者向け  
- **実装ガイド:** ALPSが開発の明確な構造を提供
- **生きているドキュメント:** コード変更が仕様を自動更新
- **契約確実性:** 実装と仕様が乖離不可能

### プロダクトチーム向け
- **ビジネス整合:** ALPSがビジネスワークフローを正確にキャプチャ
- **変更影響:** 修正が即座に仕様効果を表示
- **クライアント調整:** 生成されたAPIが明確な統合契約を提供

## 結論

ALPSとBe Framework間の双方向関係は、仕様駆動開発の根本的進歩を表しています。実装を生きている仕様として、仕様を実装可能な設計として扱うことで、ビジネス意図と技術現実の完璧な整合を達成します。

このアプローチは設計と実装間の従来のギャップを排除し、以下の統一システムを作成します：
- 仕様は常に最新
- 実装は常に準拠
- APIは常に一貫
- ドキュメントは常に正確

結果は単により良いソフトウェア開発ではありません——設計と実装の関係についての新しい考え方であり、両方が洗練と明確性の継続サイクルで互いを情報提供し強化します。

---

*次：実世界アプリケーションでのALPS-Be双方向生成の実践例を探求。*