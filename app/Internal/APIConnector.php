<?php

namespace App\GenericLibrary\AirTable\Internal;

use App\GenericLibrary\AirTable\Query;

interface APIConnector
{
    /**
     * @return Record[]
     */
    public function execute(Query $query): array;

    /**
     * @param Query[] $queries
     * @return array<string, Record[]> stringにはQueryのキャッシュキーが入っている
     */
    public function executeBatch(array $queries): array;
    public function lastExecutedQuery(): Query;
}
