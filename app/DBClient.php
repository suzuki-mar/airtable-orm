<?php

declare(strict_types=1);

namespace App\GenericLibrary\AirTable;

interface DBClient
{
    /**
     * @return array<MappingModelAbstract>
     */
    //    ModelsではなくてRecordにする
    public function fetchModels(Query $query): array;

    public function fetchModel(Query $query): MappingModelAbstract;

    /**
     * @param array<int, Query> $queries
     * @return array<string, array<int, MappingModelAbstract>>
     */
    public function fetchModelsBatch(array $queries): array;

    /**
     * @return array<MappingModelAbstract>
     */
    public function fetchModelsFromCache(string $cacheKey): array;
    public function hasCache(string $cacheKey): bool;


    public static function buildInitializedQuery(): Query;
}
