# AirTableORM
 Airtable Integration Library

このライブラリはAirtableと簡単にやり取りできるように設計されており、利用者は最小限の知識で操作が可能です。例えば、`AirTable::buildClient` でクライアントを生成し、`AirTable::buildInitializedQuery` で簡単にクエリを構築できます。

これらのメソッドは、ライブラリ内部で適切なインターフェースを返すよう設計されており、実装の詳細を意識することなく、シンプルで直感的な操作が可能です。

## 使用例

### 1. レコードの検索

特定のレコードIDを検索します。

```php
$client = AirTable::buildClient();
$query = AirTable::buildInitializedQuery();

$recordId = new RecordID('rec1234567890');
$query->whereBy($recordId)->applyMappingObject(new WrestlerName());
$model = $client->fetchModel($query);

echo $model->name->value();
```

AirTable::buildInitializedQuery();　で次のインターフェイスを返します
https://github.com/suzuki-mar/airtable-orm/blob/main/app/Query.php


### 2. 条件付き検索
特定条件で絞り込み、並べ替えと取得件数を設定します。

```php
$client = AirTable::buildClient();
$query = AirTable::buildInitializedQuery();

$query->whereEquals('fldORG_REL', '所属')
    ->orderBy('fldDEBUT_DATE', 'asc')
    ->setLimit(3)
    ->applyMappingObject(new WrestlerName());

$models = $client->fetchModels($query);

foreach ($models as $model) {
    echo $model->name->value() . PHP_EOL;
}
```

### 3. キャッシュの使用
キャッシュキーを設定してデータを保存・再取得します。

```php
$client = AirTable::buildClient();
$query = AirTable::buildInitializedQuery();

$query->whereIn('fldNAME', ['彩羽匠', '桃野美桜'])
    ->applyMappingObject(new WrestlerName())
    ->setCacheKey('cache_key');

$models = $client->fetchModels($query);
$cachedModels = $client->fetchModelsFromCache('cache_key');

foreach ($cachedModels as $model) {
    echo $model->name->value() . PHP_EOL;
}
```

### 4. 高度なフィルタリング
特定の日付範囲内のレコードを検索します。

```php
コードをコピーする
$client = AirTable::buildClient();
$query = AirTable::buildInitializedQuery();

$query->whereBetween('fldDEBUT_DATE', '2016-02-12', '2016-02-14')
    ->applyMappingObject(new WrestlerName());

$models = $client->fetchModels($query);

foreach ($models as $model) {
    echo $model->name->value() . PHP_EOL;
}
```

### 5. 並列処理
複数の検索クエリを並列実行し、結果を取得します。

```php
$client = AirTable::buildClient();

$idSearchQuery = AirTable::buildInitializedQuery();
$idSearchQuery->whereBy(new RecordID('rec1234567890'))
    ->applyMappingObject(new WrestlerName())
    ->setCacheKey('id_search');

$allSearchQuery = AirTable::buildInitializedQuery();
$allSearchQuery->all()->applyMappingObject(new WrestlerName())
    ->setCacheKey('all_search');

$results = $client->fetchModelsBatch([$idSearchQuery, $allSearchQuery]);

foreach ($results['id_search'] as $model) {
    echo 'ID Search: ' . $model->name->value() . PHP_EOL;
}

foreach ($results['all_search'] as $model) {
    echo 'All Search: ' . $model->name->value() . PHP_EOL;
}
```
