# 概要：コードについての新しい考え方

## まず、これを見てください

```php
// 従来のユーザー削除方法
$user = User::find($id);
$user->delete();

// 異なるユーザー削除方法
$activeUser = User::find($id);
$deletedUser = new DeletedUser($activeUser);
```

**DeletedUser？**

違和感を感じるなら、あなただけではありません。なぜ削除が新しいものを生み出すのでしょうか？

この違和感こそが重要なことを明らかにしています。プログラミングに関する私たちの想定があまりに深く根ざしているため、それを疑問視することがほとんどないのです。

## 違い

従来のプログラミングは**アクション**に焦点を当てます：
```php
$user->validate();
$user->save();
$user->notify();
```

Be Frameworkは**存在**に焦点を当てます：
```php
$rawData = new UserInput($_POST);
$validatedUser = new ValidatedUser($rawData);
$savedUser = new SavedUser($validatedUser);
```

一方はオブジェクトに何を**すべきか**を指示します。
もう一方は何に**なれるか**を定義しています。

## なぜこれが重要なのか

**DOING**に焦点を当てると：
- アクションが許可されているかを常にチェックする
- 無限のエラーケースを処理する
- 無効な状態と闘う

**BEING**に焦点を当てると：
- 無効な状態は存在し得ない
- オブジェクトが自身の有効性を持っている
- 存在そのものが証明である

違いは型そのものにあります：
```php
// 従来：汎用的な型
function processUser(User $user) { }

// Be Framework：存在の特定の状態
function processUser(ValidatedUser $user) { }
function saveUser(SavedUser $user) { }
function archiveUser(DeletedUser $user) { }
```

各型は単なるデータではなく、存在の特定段階を表現しています。

## 学習内容

このマニュアルでは以下の方法をお示しします：

1. **アクションを命令する**代わりに**存在を定義する**
2. **無効な状態をチェックする**代わりに**無効な状態を不可能にする**
3. **変更を強制する**代わりに**オブジェクトが自然に変換する**
4. **エラーから守る**代わりに**存在を信頼する**

## 準備はいいですか？

基礎から始めましょう：[Input クラス →](./01-input-classes.md)

あなたの最初のBeingを構築し、なぜ`DeletedUser`が完全に理にかなっているかを発見していただけるでしょう。