# Be Framework — 課題と改善提案の詳細 (2026-06-11 コードレビューより)

コードベース全体(`src/` 全クラス、`tests/`、`example/`、`concept/`)の精読と
テストスイート実行(241件グリーン、Notice 3件)に基づく課題一覧。
優先度: 🔴 P1(即対応)/ 🟠 P2(重要)/ 🟡 P3(品質向上)。

---

## 🔴 P1-1: CI にテスト/静的解析ワークフローがない

**現状**: `.github/workflows/` には `claude.yml` と `claude-code-review.yml` のみ。
`composer tests`(cs + sa + phpmd + phpunit)を実行するワークフローが存在せず、
PRマージ時の品質保証がAIレビューのみに依存している。

**提案**:
- PHP 8.3 / 8.4 マトリクスで `composer tests` を実行する標準CIワークフローを追加
- カバレッジレポート(pcov)のアップロードも検討

**作業箇所**: `.github/workflows/ci.yml`(新規)

---

## 🔴 P1-2: composer.json の autoload 漏れ

**現状** (`composer.json`):

```json
"autoload": {
    "psr-4": {
        "Be\\Framework\\": ["src/", "tests/Fake/"],
        "Koriym\\SchemaLogger\\": "src/SchemaLog/"
    }
}
```

問題点:
1. `tests/Fake/` が **本番 autoload** に含まれており、ライブラリ利用者の
   アプリケーションでテストfixtureクラスがロード可能な状態で漏れる
2. `Koriym\SchemaLogger\` がマップする `src/SchemaLog/` ディレクトリは**存在しない**

**提案**:
- `tests/Fake/` のマッピングを `autoload-dev` へ移動
  (`Be\Framework\` 名前空間をfixtureが共有しているため、dev側で同じprefixを追加する)
- `Koriym\SchemaLogger\` エントリを削除

---

## 🟠 P2-1: パラメータ名リネームでセマンティック検証が「静かに」消失する

**現状**: セマンティック検証は変数名→クラス名の規約解決
(`SemanticValidator::resolveSemanticClass()`, `src/SemanticVariable/SemanticValidator.php:275`)。
`$email` を `$mail` にリネームすると、例外もエラーもなく検証が消失する
(`trigger_error(E_USER_NOTICE)` のみ)。「存在=証明」という根幹哲学に対する唯一の穴。

また opt-in 検証の副作用として、オントロジー未登録のパラメータ**すべて**で
E_USER_NOTICE が発生する(テスト実行時の `Notices: 3` の源泉)。実アプリではノイズになる。

**提案**: 解決失敗時の挙動を3段階ポリシーで設定可能にする:
- `silent` — 何もしない(本番向け)
- `notice` — 現状の `trigger_error`(デフォルト)
- `strict` — 例外を throw(`#[Input]` パラメータがオントロジーに解決できなければ失敗)

将来的には be-lint(P3-7参照)で静的検出に昇格させるのが理想。

**作業箇所**: `src/SemanticVariable/SemanticValidator.php`、`src/Module/BeModule.php`(ポリシーのDIバインディング)

---

## 🟠 P2-2: `BeMatchException` が不一致の詳細を捨てている

**現状**: `Being::performTypeMatching()` (`src/Being.php:110`) は型不一致時に

```php
$unmatches[] = new Unmatch($class, UnmatchReason::TypeMismatch);  // details なし
```

と理由なしで記録する。一方 `BecomingType::getMismatchReasons()` は
「どのパラメータが・期待型・実際型」まで生成できるのに、
**テストからしか呼ばれておらず事実上の死蔵コード**になっている。

**提案**: `performTypeMatching()` で `match()` が false の場合に
`getMismatchReasons()` を呼び、結果を `Unmatch::$details` に渡す。
分岐失敗時のエラーメッセージが「Type mismatch in X」から
「X: parameter $age expected int, got string」になり、デバッグ体験が大幅に向上する。

**作業箇所**: `src/Being.php`、`src/Exception/Unmatch.php`

---

## 🟠 P2-3: 無限メタモルフォーゼの保護がない

**現状**: `#[Be]` で `A → B → A` のような循環を宣言すると
`Becoming::__invoke()` (`src/Becoming.php:57`) の while ループが永久に回る。

**提案**: 変容ステップ数の上限(設定可能、デフォルト例: 1000)を設け、
超過時に専用例外(例: `EndlessBecomingException`)を throw する。
例外メッセージには直近の変容チェーン(クラス名の列)を含めること。

**作業箇所**: `src/Becoming.php`、`src/Exception/`(新規例外)

---

## 🟡 P3-1: 線形変換での `#[Input]` プロパティ欠落が `null` 注入になる

**現状**: `BecomingArguments::be()` (`src/BecomingArguments.php:64`) は
ソースオブジェクトに存在しないプロパティを `null` で埋めるため、
非nullableパラメータでは PHP の生の `TypeError` が発生する。
分岐(配列)の場合は `BecomingType::match()` が事前検出するのに、
線形の場合はドメイン文脈のない低レベルエラーになる非対称がある。

**提案**: 欠落を検出し「`Source::$x` が存在しないため `Target` になれない」という
存在論的文脈を持つ専用例外に変換する。

---

## 🟡 P3-2: `ScalarParameterRequiresNamed` 例外が未使用(死蔵)

**現状**: `src/Exception/ScalarParameterRequiresNamed.php` は定義と自身のテストのみ存在し、
**どこからも throw されていない**。実際には `BecomingArguments::getInjectParameter()`
(`src/BecomingArguments.php:95`)が scalar 型 + `#[Named]` なしの場合に
`$injector->getInstance('', '')` へ落ち、Ray.Di の `Unbound` になる。

**提案**: `getInjectParameter()` で scalar 型かつ `#[Named]` なしを検出して
この例外を throw するか、例外クラス自体を削除するか、どちらかに倒す。

---

## 🟡 P3-3: `#[Be]` の `IS_REPEATABLE` 宣言と実装の不整合

**現状**: `src/Attribute/Be.php:23` は `Attribute::IS_REPEATABLE` を宣言しているが、
`Being::willBe()` (`src/Being.php:45`) は `$beAttributes[0]` のみを読む。
2つ目以降の `#[Be]` は黙って無視される。

**提案**: 複数の `#[Be]` を候補配列にマージして尊重するか、
`IS_REPEATABLE` を外すか、どちらかに統一する。

---

## 🟡 P3-4: `Becoming` と `Logger` がそれぞれ別の `Being` を生成している

**現状**: `Becoming` のコンストラクタが `Being` を生成し(`src/Becoming.php:36`)、
`Logger` も内部で独自の `Being` を生成している(`src/SemanticLog/Logger.php:59`)。
Logger 側の `Being` は `willBe()`(= `#[Be]` 属性の読み取り)にしか使われない。
概念的な相互依存(Being ⇄ Logger)を二重生成で回避している設計臭。

**提案**: `willBe()` は実質「Be属性リーダー」なので、純粋関数的な独立クラス
(例: `Destiny` / `BeReader`)に抽出し、`Being` と `Logger` の双方がそれを使う。
相互依存が解消され、Logger から `BecomingArgumentsInterface` 依存も外せる可能性がある。

---

## 🟡 P3-5: `BecomingType` の型互換エッジケース

**現状** (`src/BecomingType.php`):
- `int → float` ワイドニングを不一致と判定する(strict_types でもPHPは合法)
- `iterable` 期待 + `array` 実値 → 不一致と判定
- `callable` は `instanceof 'callable'` が常に false になるため Closure でもマッチしない

**提案**: `isBuiltInTypeCompatible()` に `int→float`、`array/Traversable→iterable`、
`Closure→callable` の互換ルールを追加する。

---

## 🟡 P3-6: レガシーAPI・BCコードの整理(v0 の今が好機)

**現状**:
- `SemanticValidator` のコンストラクタ第2引数が
  `array|SemanticValidationMethodResolver|null $classMapOrValidationMethodResolver`
  というBC用ユニオンで分かりにくい(`src/SemanticVariable/SemanticValidator.php:47`)
- `@deprecated` メソッド群(`validate` / `validateLegacy` / `validateObject` /
  `validateAndThrow`)が `SemanticValidator` と `NullValidator` に残存
  (インターフェイスには含まれていない)
- `BeMatchException::getCandidateErrors()` も `@deprecated`

**提案**: 1.0 前の今、シグネチャを整理しレガシーメソッドを削除する。
PHP 8.4 ターゲットなら `#[\Deprecated]` ネイティブ属性への移行も選択肢。

---

## 🟡 P3-7: 分岐の曖昧性検出がない(先勝ち仕様が暗黙)

**現状**: `#[Be([A, B])]` で両候補がマッチする場合、配列順で先勝ちし黙って A になる。

**提案**: 仕様として「配列順 = 優先順位」を文書化するか、
複数マッチ検出時に throw する strict オプションを設ける
(「存在は一意に定まる」という哲学にも適合)。

---

## 🟡 P3-8: `example/` の鮮度

**現状**:
- `example/Being/FormalGreeting.php` がコンストラクタ内で手動
  `new SemanticValidator(...)` を実行 — フレームワークが `BecomingArguments` で
  自動実行する検証と重複し、現在の設計思想と矛盾するデモになっている
- `example/Being/BeGreeting.php` に `==` の緩比較(`$style == 'formal'`)
- 複数ファイルに未使用 `use` 文

**提案**: 「最初に読まれるコード」なので、自動セマンティック検証だけに頼る形に
書き直し、`===`・未使用import を整理する。`composer cs-fix` 対象に example/ を含める。

---

## 🟡 P3-9: リフレクションのプラン・キャッシュ(性能)

**現状**: 1変容ステップあたり同一クラスへの `new ReflectionClass` が5〜6回発生
(`BecomingArguments::be`、`Logger::open` 内の immanent/transcendent/Be属性チェックで3回、
`Being::performSingleTransformation` の `newInstanceArgs`、`BecomingType::match`)。
現状の規模では問題ない(テスト全体100ms)が、本番高スループットでは無駄。

**提案**: クラスごとの「変容プラン」(パラメータ名・属性種別・型情報のメモ化)を
導入し、ステップ実行をプラン参照のみにする。Ray.Di のコンパイル思想の踏襲。
将来的には本番向けのプリコンパイルも視野。

---

## 戦略的な可能性(issue ではなく投資テーマ)

1. **LDDツールチェーン**: `LddLoopTest` が証明した「been.json = 仕様 = 実例 = テスト」
   のループをCLI化する — `be generate <been.json>`(spec→クラス雛形)、
   `be record`(実行→been.json採取・契約テスト化)。AI協調開発の理想基盤。
2. **be-lint / 可視化**: `#[Be]` グラフの静的解析(到達不能Being・循環・
   `#[Input]` プロパティのフロー検証・オントロジー未登録の静的検出)と
   Mermaid 状態遷移図の自動生成。
3. **中断・再開可能なメタモルフォーゼ**: 全状態が `public readonly` = 完全に
   シリアライズ可能。キュー境界でチェーンを切断・再開する Saga/長時間ワークフロー。
4. **アダプタ層**: PSR-15 ブリッジ、BEAR.Sunday 統合(別パッケージで)。
5. **オントロジーの資産化**: セマンティック変数からのプロパティベーステスト自動生成、
   `SemanticTag` の `@todo` にある ALPS descriptor 双方向生成。

---

## 推奨着手順

1. P1-1(CI)→ P1-2(autoload)
2. P2-1(strictポリシー)→ P2-2(BeMatchException詳細)→ P2-3(ループガード)
3. P3 群はまとめて1.0前のクリーンアップとして
