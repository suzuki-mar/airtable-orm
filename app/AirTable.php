<?php

declare(strict_types=1);

namespace App\GenericLibrary\AirTable;

use App\GenericLibrary\AirTable\Internal\APIConnector;
use App\GenericLibrary\AirTable\Internal\APIConnector\Real;
use App\GenericLibrary\AirTable\Internal\DBClient as ClientConcrete;
use App\GenericLibrary\AirTable\Internal\Query as QueryConcrete;

final class AirTable
{
    public static function buildApiConnector(): APIConnector
    {
        $baseId = config('services.airtable.base_id');
        if ($baseId === null) {
            throw new \InvalidArgumentException('AirTable Base ID is not set');
        }
        if (! is_string($baseId)) {
            throw new \InvalidArgumentException('AirTable Base ID is not string');
        }

        $apiKey = config('services.airtable.api_key');
        if ($apiKey === null) {
            throw new \InvalidArgumentException('AirTable API Key is not set');
        }
        if (! is_string($apiKey)) {
            throw new \InvalidArgumentException('AirTable API Key is not string');
        }

        return new Real($apiKey, $baseId);
    }

    public static function buildClient(APIConnector $apiConnector): DBClient
    {
        return new ClientConcrete($apiConnector);
    }

    public static function buildInitializedQuery(): Query
    {
        return new QueryConcrete();
    }
}
