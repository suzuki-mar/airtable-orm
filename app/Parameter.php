<?php

namespace App\GenericLibrary\AirTable;

use App\GenericLibrary\AirTable\Internal\Query as QueryConcrete;
use App\GenericLibrary\AirTable\Query as QueryInterface;

final readonly class Parameter
{
    public string $filterByFormula;
    /**
     * @var array<int, array{"field": string, "direction": "asc"|"desc"}>
     */
    public array $sort;
    public int $maxRecords;
    private QueryConcrete $query;

    public function __construct(QueryInterface $query)
    {
        assert($query instanceof QueryConcrete);
        $this->query           = $query;

        $this->filterByFormula = $this->buildFilter();
        $this->sort            = $this->buildSort();
        $this->maxRecords      = $this->query->limit();
    }

    public function buildFilter(): string
    {
        if (count($this->query->queries) == 0 && $this->query->targetRecordID === null && ! $this->query->isAll) {
            throw new \InvalidArgumentException('Query is empty');
        }

        //        主キーでの検索はクエリーはいらない
        if ($this->query->targetRecordID !== null) {
            return '';
        }

        if (count($this->query->queries) == 1) {
            return $this->query->queries[0];
        }

        $conditionsStr = implode(',', $this->query->queries);
        return "AND({$conditionsStr})";
    }

    /**
     * @return array<int, array{"field": string, "direction": "asc"|"desc"}>
     */
    private function buildSort(): array
    {
        if (count($this->query->orderByList) == 0) {
            return [];
        }

        $result = [];
        foreach ($this->query->orderByList as $order) {
            $result[] = [
                'field'     => $order['field'],
                'direction' => $order['type'],
            ];
        }

        return $result;
    }

    public function __toString(): string
    {
        $sortStr = implode(', ', array_map(function ($item) {
            return "{$item['field']} ({$item['direction']})";
        }, $this->sort));

        return "filterByFormula: {$this->filterByFormula}, sort: {$sortStr}, maxRecords: {$this->maxRecords}";
    }
}
