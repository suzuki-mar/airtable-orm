<?php

use App\Competition\DB\Wrestler;
use App\Competition\DB\WrestlerFieldMapping;
use App\Competition\DB\WrestlerName;
use App\GenericLibrary\AirTable\AirTable;
use App\GenericLibrary\AirTable\DBClient;
use App\GenericLibrary\AirTable\Field\Date;
use App\GenericLibrary\AirTable\Field\RecordID;
use App\GenericLibrary\AirTable\MappingModelAbstract;
use App\GenericLibrary\AirTable\Parameter;
use Tests\Competition\WrestlerTestData;
use Tests\GenericLibrary\AirTable\TestDependenciesBuilder;
use Tests\GenericLibrary\AirTable\Faker;
use Webmozart\Assert\Assert;

//
//テスト時間がながくならいように最小限のみAPIにパラメーターを送信している
describe('APIと直接接続をするテスト時間を短くするためにAPIに送信パラメーターがあっているかだけを確認する', function () {
    test('ID検索', function () {
        $client   = app(DBClient::class);
        $query    = AirTable::buildInitializedQuery();
        $recordId = new RecordID(WrestlerTestData::RECORD_ID);
        $query->whereBy($recordId)->applyMappingObject(new WrestlerName());

        $model = $client->fetchModel($query);
        expect($model)->toBeInstanceOf(WrestlerName::class);
    });

    test('ソートとリミット', function () {
        $client   = app(DBClient::class);
        $query    = AirTable::buildInitializedQuery();
        $query->whereEquals(WrestlerFieldMapping::FIELD_ORG_RELATIONSHIP_ID, Wrestler::ORG_RELATIONSHIP_AFFILIATION)
            ->orderBy(WrestlerFieldMapping::FIELD_DEBUT_DATE_ID, 'asc')->setLimit(3)
            ->applyMappingObject(new WrestlerName());
        $models = $client->fetchModels($query);

        Assert::allIsInstanceOf($models, WrestlerName::class);

        $names = collect($models)->map(function (WrestlerName $model) {
            return $model->name->value();
        })->toArray();

        //        配列のIndexなので -1している
        expect($names[WrestlerTestData::AFFILIATION_ORDER - 1])->toBe(WrestlerTestData::FULL_NAME);
    });

    test('すべて取得', function () {
        $dependency = TestDependenciesBuilder::build();

        $dependency['query']->all()->applyMappingObject(new WrestlerName());
        $models = $dependency['realClient']->fetchModels($dependency['query']);

        Assert::allIsInstanceOf($models, WrestlerName::class);

        expect(count($models))->toBeGreaterThan(0);
    });

    test('並列に実行をする', function () {
        $client           = app(DBClient::class);
        $idSearchQuery    = AirTable::buildInitializedQuery();
        $recordId         = new RecordID(WrestlerTestData::RECORD_ID);
        $idSearchQuery->whereBy($recordId)->applyMappingObject(new WrestlerName())->setCacheKey('idSearch');

        $allSearchQuery    = AirTable::buildInitializedQuery();
        $allSearchQuery->all()->applyMappingObject(new WrestlerName())->setCacheKey('allSearch');


        $results = $client->fetchModelsBatch([$idSearchQuery, $allSearchQuery]);
        Assert::allIsInstanceOf($results['idSearch'], WrestlerName::class);
        expect($results['idSearch'])->toHaveCount(1);

        Assert::allIsInstanceOf($results['allSearch'], WrestlerName::class);
        expect(count($results['allSearch']) > 1)->toBeTrue();
    });

    test('キャッシュから取得する', function () {
        $testDependencies = TestDependenciesBuilder::build();
        $targetValues     = ['彩羽匠', '桃野美桜'];
        $cacheKey         = 'cacheKey';

        $testDependencies['query']->whereIn(WrestlerFieldMapping::FIELD_NAME_ID, $targetValues)
            ->applyMappingObject(new WrestlerName())->setCacheKey($cacheKey);

        $wrestlers      = $testDependencies['realClient']->fetchModels($testDependencies['query']);
        $cacheWrestlers = $testDependencies['realClient']->fetchModelsFromCache($cacheKey);

        expect($cacheWrestlers)->toEqual($wrestlers)
            ->and($testDependencies['realClient']->hasCache($cacheKey))->toBeTrue();
    });
})->group('external');

describe('送信パラメーターがあっているかの確認をする', function () {
    test('ワード検索', function () {
        $testDependencies = TestDependenciesBuilder::build();

        $testDependencies['query']->whereContains(WrestlerFieldMapping::FIELD_NAME_ID, WrestlerTestData::FIRST_NAME)
            ->applyMappingObject(new WrestlerName());

        $testDependencies['mockClient']->fetchModel($testDependencies['query']);
        $parameter = new Parameter($testDependencies['apiMockConnector']->lastExecutedQuery());
        $firstName = WrestlerTestData::FIRST_NAME;
        expect($parameter->filterByFormula)->toEqual("SEARCH('{$firstName}', {fldEFSGh1IhevNL4J})");
    });

    test('一致検索', function () {
        $relationAffiliation = Wrestler::ORG_RELATIONSHIP_AFFILIATION;
        $filedId             = WrestlerFieldMapping::FIELD_ORG_RELATIONSHIP_ID;

        $testDependencies = TestDependenciesBuilder::build();
        $testDependencies['query']->whereEquals($filedId, $relationAffiliation)->applyMappingObject(new WrestlerName());

        $model     = $testDependencies['mockClient']->fetchModel($testDependencies['query']);
        $parameter = new Parameter($testDependencies['apiMockConnector']->lastExecutedQuery());

        expect($model)->toBeInstanceOf(MappingModelAbstract::class)
            ->and($parameter->filterByFormula)->toEqual("{$filedId} = '{$relationAffiliation}'");
    });

    test('IDリスト検索', function () {
        $orgAffiliationIds = [
            new RecordID('rec3AQTkh9yq7qj7p'),
            new RecordID('rec94QzPBQydWzMwb'),
        ];

        $testDependencies = TestDependenciesBuilder::build();

        $testDependencies['query']->whereByIds($orgAffiliationIds)->applyMappingObject(new WrestlerName());

        $model     = $testDependencies['mockClient']->fetchModel($testDependencies['query']);
        $parameter = new Parameter($testDependencies['apiMockConnector']->lastExecutedQuery());

        $expectedFilter = "OR(RECORD_ID() = 'rec3AQTkh9yq7qj7p',RECORD_ID() = 'rec94QzPBQydWzMwb')";
        expect($model)->toBeInstanceOf(WrestlerName::class)
            ->and($parameter->filterByFormula)->toEqual($expectedFilter);
    });

    test('同じフィールドから取得する', function () {
        $testDependencies = TestDependenciesBuilder::build();
        $targetValues     = ['彩羽匠', '桃野美桜'];

        $testDependencies['query']->whereIn(WrestlerFieldMapping::FIELD_NAME_ID, $targetValues)
            ->applyMappingObject(new WrestlerName());

        $model     = $testDependencies['mockClient']->fetchModel($testDependencies['query']);

        $parameter = new Parameter($testDependencies['apiMockConnector']->lastExecutedQuery());
        $filedId   = WrestlerFieldMapping::FIELD_NAME_ID;

        expect($parameter->filterByFormula)->toEqual(
            "OR({$filedId} = '{$targetValues[0]}',{$filedId} = '{$targetValues[1]}')"
        )
            ->and($model)->toBeInstanceOf(WrestlerName::class);
    });

    test('日付が同じものの検索', function () {
        $testDependencies = TestDependenciesBuilder::build();

        $testDependencies['query']->whereSameDay(
            WrestlerFieldMapping::FIELD_DEBUT_DATE_ID,
            new Date(WrestlerTestData::DEBUT_DATE)
        )->applyMappingObject(new WrestlerName());

        $model     = $testDependencies['mockClient']->fetchModel($testDependencies['query']);

        $parameter = new Parameter($testDependencies['apiMockConnector']->lastExecutedQuery());

        $filedId = WrestlerFieldMapping::FIELD_DEBUT_DATE_ID;
        expect($parameter->filterByFormula)->toEqual("IS_SAME({$filedId}, '2016-02-13')")
            ->and($model)->toBeInstanceOf(WrestlerName::class);
    });

    test('日付が前のもの検索', function () {

        $testDependencies  = TestDependenciesBuilder::build();
        $previousDay       = Faker::previousDay(WrestlerTestData::DEBUT_DATE);
        $testDependencies['query']->whereBefore(WrestlerFieldMapping::FIELD_DEBUT_DATE_ID, $previousDay)
            ->applyMappingObject(new WrestlerName());

        $model     = $testDependencies['mockClient']->fetchModel($testDependencies['query']);
        $parameter = new Parameter($testDependencies['apiMockConnector']->lastExecutedQuery());

        $filedId   = WrestlerFieldMapping::FIELD_DEBUT_DATE_ID;
        expect($parameter->filterByFormula)->toEqual("IS_BEFORE({$filedId}, '2016-02-12')")
            ->and($model)->toBeInstanceOf(WrestlerName::class);
    });

    test('日付があとのものの検索', function () {
        $nextDay          = Faker::nextDay(WrestlerTestData::DEBUT_DATE);
        $testDependencies = TestDependenciesBuilder::build();

        $testDependencies['query']->whereAfter(WrestlerFieldMapping::FIELD_DEBUT_DATE_ID, $nextDay)
            ->applyMappingObject(new WrestlerName());

        $models    = $testDependencies['mockClient']->fetchModel($testDependencies['query']);
        $parameter = new Parameter($testDependencies['apiMockConnector']->lastExecutedQuery());
        $filedId   = WrestlerFieldMapping::FIELD_DEBUT_DATE_ID;
        expect($parameter->filterByFormula)->toEqual("IS_AFTER({$filedId}, '2016-02-14')")
            ->and($models)->toBeInstanceOf(WrestlerName::class);
    });

    test('日付の範囲検索', function () {
        $previousDay = Faker::previousDay(WrestlerTestData::DEBUT_DATE);
        $nextDay     = Faker::nextDay(WrestlerTestData::DEBUT_DATE);


        $testDependencies = TestDependenciesBuilder::build();
        $testDependencies['query']->whereBetween(WrestlerFieldMapping::FIELD_DEBUT_DATE_ID, $previousDay, $nextDay)
            ->applyMappingObject(new WrestlerName());

        $model     = $testDependencies['mockClient']->fetchModel($testDependencies['query']);

        $parameter      = new Parameter($testDependencies['apiMockConnector']->lastExecutedQuery());
        $filedId        = WrestlerFieldMapping::FIELD_DEBUT_DATE_ID;
        $expectedFilter = "AND(IS_AFTER({$filedId}, '2016-02-12'), IS_BEFORE({$filedId}, '2016-02-14'))";
        expect($parameter->filterByFormula)->toEqual($expectedFilter)
            ->and($model)->toBeInstanceOf(WrestlerName::class);
    });

    test('AND検索', function () {
        //       テストの実行確認時にメッセージを作成するために変数に代入しいている
        $affiliationRelationship =  Wrestler::ORG_RELATIONSHIP_AFFILIATION;
        $targetName              = WrestlerTestData::FIRST_NAME;
        $relationField           = WrestlerFieldMapping::FIELD_ORG_RELATIONSHIP_ID;
        $nameField               = WrestlerFieldMapping::FIELD_NAME_ID;
        $testDependencies        = TestDependenciesBuilder::build();

        $testDependencies['query']->whereEquals($relationField, $affiliationRelationship)
            ->whereContains($nameField, $targetName)
            ->applyMappingObject(new WrestlerName());

        $model     = $testDependencies['mockClient']->fetchModel($testDependencies['query']);
        $parameter = new Parameter($testDependencies['apiMockConnector']->lastExecutedQuery());


        // phpcs:disable Generic.Files.LineLength
        $expectedFilter = "AND({$relationField} = '{$affiliationRelationship}',SEARCH('{$targetName}', {{$nameField}}))";
        expect($model)->toBeInstanceOf(WrestlerName::class)
            ->and($parameter->filterByFormula)->toEqual($expectedFilter);
    });
});
