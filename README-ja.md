# Be Framework コンセプト

> 「Be, Don't Do」— プログラミングが無為（Wu Wei）の原理と調和するとき

Be Framework は、存在論的プログラミングパラダイムを実装するPHPフレームワークです。データ変換を純粋なコンストラクタ駆動メタモルフォーシスとして実現します。

## 哲学

Be Framework は、シンプルでありながら深遠な疑問から生まれました：何が起こるべきかではなく、何が存在できるかを定義することでプログラムを書いたらどうなるのでしょうか？

Ray.Di の依存性注入パターンの哲学的基盤の上に構築された Be Framework は、すべてのデータ変換をメタモルフォーシス（変態）として扱います。コンストラクタ注入を通じた継続的な「becoming（なりつつあること）」として実現します。各オブジェクトは避けられない前提を受け入れ、becoming のプロセスを通じて新しく完璧な形へと自らを変換します。

Be Framework では、すべての変換は以下の相互作用から生まれます：

**内在性** — オブジェクトが既に持っているもの（そのアイデンティティ）

**超越性** — 世界が提供するもの（コンテキスト、能力）

これは、世界における存在が意味ある存在になる過程を反映しています：内在性だけでなく、自分を超えた何かとの出会いによって実現されるのです。

## 中核概念

### Being クラス

Be Framework のすべてのクラスは Being クラスです——自己完結型で不変な存在と変換の段階：

```php
#[Be(Greeting::class)]  // 運命
final class NameInput
{
    public function __construct(
        public readonly string $name  // 内在性
    ) {}
}

final class Greeting
{
    public readonly string $message;
    
    public function __construct(
        #[Input] string $name,                // 内在性
        #[Inject] WorldGreeting $greeting     // 超越性
    ) {
        $this->message = "{$greeting->text} {$name}";  // 新しい内在性
    }
}
```

### Becoming の実行

```php
// メタモルフォーシスを実行
$becoming = new Becoming($injector);
$finalObject = $becoming(new NameInput('world'));

echo $finalObject->message; // hello world
```

## 主要原則

1. **「Be, Don't Do」**：アクションシーケンスではなく、存在状態を定義することでプログラム
2. **変化よりメタモルフォーシス**：オブジェクトは状態を変更するのではなく、新しい存在へと変換
3. **存在論的プログラミング**：ドメインと時間は分離できない - オブジェクトは時間的に存在
4. **時間的存在**：生まれる前に死ぬことはできない - オブジェクトは自然なライフサイクルに従う
5. **内在性 + 超越性 = 新しい内在性**：すべての変換は内的本質と外的能力を結合

## ドキュメント

### 基礎
**[学術論文](docs/papers/)** - 理論的および哲学的基盤  
**[哲学的基盤](docs/reference/)** - 影響と洞察

### 実践ガイド  
**[Be Framework マニュアル](docs/manual/index.md)** - チュートリアルレベルの実装ガイド  
**[完全ドキュメントガイド](docs/README.md)** - 包括的な読書パス  
**[AI 支援学習](docs/study/README.md)** - インタラクティブな探求（[日本語](docs/study/README-ja.md)）

## 例題

- **[ユーザー登録](examples/user-registration/)**  
  型駆動メタモルフォーシスとコンストラクタバリデーションを実証する完全実装

## 音声コンテンツ

**[Be Framework ポッドキャストシリーズ](docs/study/podcast/)**  
存在論的プログラミングの概念と哲学的基盤を探求する AI 生成音声入門（英語、各回約30分）

## 主要パラダイムシフト

Be Framework は、プログラムについての考え方における根本的シフトを表現しています：

- **doing** から **being** へ
- **指示** から **変換** へ
- **可変状態** から **不変存在** へ
- **複雑なライフサイクル** から **シンプルなメタモルフォーシス** へ

## ステータス

Be Framework は現在、概念と初期実装段階にあります。ここで紹介されたアイデアは、プログラミングの性質についての深い対話から生まれ、アプリケーション構築への新しいアプローチを表現しています。

