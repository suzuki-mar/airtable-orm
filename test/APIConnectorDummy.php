<?php

declare(strict_types=1);

namespace Tests\GenericLibrary\AirTable;

use App\GenericLibrary\AirTable\Internal\APIConnector;
use App\GenericLibrary\AirTable\Query;
use App\GenericLibrary\AirTable\Internal\Query as QueryContract;
use Webmozart\Assert\Assert;

// 直接HTTPと接続をしないですませるため
// インターフェースを実装しているクラスを作成するほうがコードを追いやすくなると判断
class APIConnectorDummy implements APIConnector
{
    private Query $lastExecutedQuery;

    private readonly array $mockRecords;

    public function __construct(array $mockRecords)
    {
        $this->mockRecords = $mockRecords;
    }


    #[\Override]
    public function execute(Query $query): array
    {
        $this->lastExecutedQuery = $query;
        return $this->mockRecords;
    }

    #[\Override]
    public function executeBatch(array $queries): array
    {
        Assert::allIsInstanceOf($queries, QueryContract::class);

        $results = [];
        foreach ($queries as $query) {
            $results[$query->getCacheKey()] = $this->execute($query);
        }
        return $results;
    }

    #[\Override]
    public function lastExecutedQuery(): Query
    {
        return $this->lastExecutedQuery;
    }
}
