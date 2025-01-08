<?php

namespace App\GenericLibrary\AirTable\Internal\APIConnector;

use App\GenericLibrary\AirTable\Internal\APIConnector;
use App\GenericLibrary\AirTable\Internal\Query as QueryConcrete;
use App\GenericLibrary\AirTable\Internal\JsonRecord;
use App\GenericLibrary\AirTable\Query;
use App\GenericLibrary\AirTable\Internal\Record;
use App\GenericLibrary\AirTable\Parameter;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\Utils;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Client as HttpClient;
use RuntimeException;
use Webmozart\Assert\Assert;

final class Real implements APIConnector
{
    private readonly string $apiKey;
    private readonly string $baseId;
    private readonly HttpClient $httpClient;

    private Query $lastExecutedQuery;


    public function __construct(string $apiKey, string $baseId)
    {
        $this->apiKey = $apiKey;
        $this->baseId = $baseId;

        $this->httpClient = new HttpClient([
            'base_uri' => 'https://api.airtable.com/v0/',
            'headers'  => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept'        => 'application/json',
            ],
        ]);
    }

    #[\Override]
    public function execute(Query $query): array
    {
        assert($query instanceof QueryConcrete);

        try {
            $fetchComponents = $this->prepareFetchComponents($query);

            if ($query->targetRecordID() !== null) {
                $response =  $this->httpClient->get($fetchComponents['url']);
            } else {
                $response = $this->httpClient->get($fetchComponents['url'], $fetchComponents['params']);
            }
        } catch (GuzzleException $e) {
            $errorParameter = new Parameter($query);
            throw new RuntimeException(
                "Failed to fetch data from AirTable: {$e->getMessage()} query: {$errorParameter}"
            );
        }
        $this->lastExecutedQuery = $query;
        $contents                = $response->getBody()->getContents();

        return $this->buildRecords($contents);
    }

    #[\Override]
    public function executeBatch(array $queries): array
    {
        Assert::AllIsInstanceOf($queries, QueryConcrete::class);

        $promises = $this->buildAsyncRequests($queries);
        $results  = [];

        try {
            $responses = Utils::unwrap($promises);

            foreach ($responses as $cacheKey => $response) {
                assert(is_string($cacheKey));
                assert($response instanceof ResponseInterface);
                $contents           = $response->getBody()->getContents();
                $results[$cacheKey] = $this->buildRecords($contents);
            }
        } catch (\Throwable  $e) {
            # TODO エラーメッセージをわかりやすくする
            throw new RuntimeException(
                "Failed to fetch data from AirTable in batch: {$e->getMessage()}"
            );
        }

        return $results;
    }

    #[\Override]
    public function lastExecutedQuery(): Query
    {
        return $this->lastExecutedQuery;
    }


    /**
     * @return array{
     *     url: string,
     *     params: array{
     *         query: array{
     *             filterByFormula: string,
     *             sort: array<int, array{
     *                 field: string,
     *                 direction: 'asc'|'desc'
     *             }>,
     *             maxRecords: int
     *         }
     *     }
     * }
     */
    private function prepareFetchComponents(QueryConcrete $query): array
    {
        $url = "https://api.airtable.com/v0/{$this->baseId}/{$query->tableId()->value()}";
        if ($query->targetRecordID() !== null) {
            $url .= '/' . $query->targetRecordID()->value();
        }

        $parameter       = new Parameter($query);
        $filterByFormula = $query->isAll() ? '' : $parameter->filterByFormula;


        $params = [
            'query' => [
                'filterByFormula' => $filterByFormula,
                'sort'            => $parameter->sort,
                'maxRecords'      => $parameter->maxRecords,
            ],
        ];

        return [
            'url'        => $url,
            'params'     => $params,
        ];
    }

    /**
     * @return Record[]
     * @throw \InvalidArgumentException
     */
    public function buildRecords(string $contents): array
    {
        /** @var array<string, mixed> $responseData */
        $responseData = json_decode($contents, true);
        APIConnector\Real\Assertion::assertionResponseData($responseData);

        /** @var array<int, array<string, array<string, array<string, string>|string>|string>> $jsonRecordArray */
        $jsonRecordArray = $responseData['records'] ?? [$responseData];
        $jsonRecords     = JsonRecord::buildFromRecords($jsonRecordArray);

        $records = [];
        foreach ($jsonRecords as $record) {
            $records[] = Record::buildFromJSONRecord($record);
        };

        return $records;
    }

    /**
     *
     * @param QueryConcrete[] $queries
     * @return array<string, PromiseInterface>
     */
    private function buildAsyncRequests(array $queries): array
    {
        $promises = [];

        foreach ($queries as $query) {
            $this->lastExecutedQuery = $query;

            $fetchComponents = $this->prepareFetchComponents($query);

            if ($query->targetRecordID() !== null) {
                $promises[$query->getCacheKey()] = $this->httpClient->getAsync($fetchComponents['url']);
            } else {
                $promises[$query->getCacheKey()] = $this->httpClient->getAsync(
                    $fetchComponents['url'],
                    $fetchComponents['params']
                );
            }
        }

        return $promises;
    }
}
