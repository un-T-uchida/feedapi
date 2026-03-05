# FeedAPI

クロスドメインのRSSフィードを取得するためのプロキシAPIです。

## 概要

このAPIは、CORS（Cross-Origin Resource Sharing）の制約により、通常はJavaScriptから直接アクセスできない外部ドメインのRSSフィードを取得し、JSONフォーマットで返すプロキシとして機能します。

## 仕様

- **言語**: PHP
- **入力**: RSS/AtomフィードのURL（GETパラメータ）
- **出力**: JSON形式のフィードデータ
- **HTTPメソッド**: GET

## 機能

1. **RSSフィード取得**: 指定されたURLからRSSフィードを取得
2. **名前空間処理**: XML名前空間を適切に処理（`atom:`以外の名前空間プレフィックスを`_`に変換）
3. **JSON変換**: XMLデータをJSONに変換して返却
4. **リファラーチェック**: 同一ドメインからのリクエストのみ許可
5. **エラーハンドリング**: 不正なリクエストに対する適切なレスポンス

## 使い方

### 基本的な使用方法

```
GET /index.php?url=【RSSフィードのURL】
```

### 例

```javascript
// JavaScriptでの使用例
const feedUrl = 'https://example.com/rss.xml';
const apiUrl = `https://your-server.example.com/index.php?url=${encodeURIComponent(feedUrl)}`;

fetch(apiUrl)
  .then(response => response.json())
  .then(data => {
    console.log(data);
    // フィードデータの処理
  })
  .catch(error => {
    console.error('エラー:', error);
  });
```

### レスポンス

#### 成功時（HTTP 200 OK）
```json
{
  "channel": {
    "title": "サンプルフィード",
    "description": "フィードの説明",
    "item": [
      {
        "title": "記事タイトル1",
        "description": "記事の説明",
        "link": "https://example.com/article1"
      }
    ]
  }
}
```

#### エラー時（HTTP 400 Bad Request）
URLパラメータが指定されていない場合：
```json
{
  "message": "無効なリクエストです"
}
```

#### エラー時（HTTP 403 Forbidden）
許可されていないドメインからのアクセスの場合：
```json
{
  "message": "アクセスが許可されていません"
}
```

## 注意事項

- URLパラメータが必須です
- 取得先のRSSフィードが有効なXML形式である必要があります
- サーバーの`allow_url_fopen`が有効になっている必要があります
- **同一ドメインからのリクエストのみ許可されます**（HTTPリファラーによる制限）

## セキュリティ考慮事項

- **リファラーチェック**: HTTPリファラーヘッダーを検証し、同一ドメイン（`HTTP_HOST`）からのリクエストのみ許可しています
- リファラーチェックはブラウザベースのアクセス制御として機能しますが、リファラーヘッダーは偽装可能なため、完全なセキュリティ対策ではありません
- 本番環境での使用時は、レート制限の実装を検討してください

### SSRF対策

以下のSSRF（Server-Side Request Forgery）対策を実装しています：

- **スキーム制限**: `http`および`https`のみ許可（`file://`、`gopher://`等をブロック）
- **プライベートIP・ループバックのブロック**: 内部ネットワーク（`10.0.0.0/8`、`172.16.0.0/12`、`192.168.0.0/16`）およびループバック（`127.0.0.0/8`）へのアクセスを禁止
- **リダイレクト追跡の無効化**: リダイレクトによるIP制限・スキーム制限の回避を防止