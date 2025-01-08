<?php

declare(strict_types=1);

namespace App\GenericLibrary\AirTable\Internal;

use App\GenericLibrary\AirTable\DBClient as ClientInterface;
use App\GenericLibrary\AirTable\MappingModelAbstract;
use App\GenericLibrary\AirTable\Query as QueryInterface;

final class DBClient implements ClientInterface
{
    private APIConnector $apiConnector;

    /**
     * @var array<string, array<MappingModelAbstract>>
     */
    private array $caches = [];

    public function __construct(APIConnector $apiConnector)
    {
        $this->apiConnector = $apiConnector;
    }

    # ライブラリーの利用者はQueryを組み立てて実行をすることだけに関心があるので、インターフェースを受け取っってキャストしている
    #[\Override]
    public function fetchModels(QueryInterface $query): array
    {
        assert($query instanceof Query);
        $records = $this->apiConnector->execute($query);


        $mappingObject = $this->mapRecords($records);

        $this->caches[$query->getCacheKey()] = $mappingObject;
        return $mappingObject;
    }



    #[\Override]
    public function fetchModel(QueryInterface $query): MappingModelAbstract
    {
        return $this->fetchModels($query)[0];
    }


    #[\Override]
    public function fetchModelsBatch(array $queries): array
    {
        if (! array_is_list($queries)) {
            throw new \RuntimeException('Queries must be associative array');
        }

        $results = [];
        $records = $this->apiConnector->executeBatch($queries);
        foreach ($records as $cacheKey => $record) {
            $mappingObject           = $this->mapRecords($record);
            $this->caches[$cacheKey] = $mappingObject;
            $results[$cacheKey]      = $mappingObject;
        }

        return $results;
    }

    #[\Override]
    public function fetchModelsFromCache(string $cacheKey): array
    {
        if (! isset($this->caches[$cacheKey])) {
            throw new \RuntimeException('Cache not found');
        }

        return $this->caches[$cacheKey];
    }

    #[\Override]
    public function hasCache(string $cacheKey): bool
    {
        return isset($this->caches[$cacheKey]);
    }

    #[\Override]
    public static function buildInitializedQuery(): QueryInterface
    {
        return new Query();
    }

    /**
     * @param Record[] $records
     * @return array<int, MappingModelAbstract>
     */
    private function mapRecords(array $records): array
    {
        $mappedRecords = [];
        foreach ($records as $record) {
            $mappingObject = clone $this->apiConnector->lastExecutedQuery()->mappingObject();
            $mappingObject->assignValues($record);
            $mappedRecords[] = $mappingObject;
        }
        return $mappedRecords;
    }
}
