<?php

declare(strict_types=1);

namespace App\GenericLibrary\AirTable\Internal;

use App\GenericLibrary\AirTable\Field\Date;
use App\GenericLibrary\AirTable\Field\RecordID;
use App\GenericLibrary\AirTable\MappingModelAbstract;
use App\GenericLibrary\AirTable\Query as QueryInterface;
use App\GenericLibrary\AirTable\TableID;

final class Query implements QueryInterface
{
    /** @var string[] */
    public array $queries            = [];
    public ?RecordID $targetRecordID = null;
    /**
     * @var array<int, array{"field": string, "type": "asc"|"desc"}>
     */
    public array $orderByList = [];
    public bool $isAll        = false;
    private string $cacheKey;

    //  AirTableのデフォルトは100
    private int $limit = 100;

    private MappingModelAbstract $mappingObject;

    public function __construct()
    {
        //        nullかどうかのチェックをしないですませるため 指定がない場合はランダムなキーを生成
        $this->cacheKey = md5(uniqid('', true));
    }

    #[\Override]
    public function whereContains(string $field, string $value): self
    {
        $this->queries[] = "SEARCH('{$value}', {{$field}})";
        return $this;
    }

    #[\Override]
    public function whereBy(RecordID $recordID): self
    {
        $this->targetRecordID = $recordID;
        return $this;
    }

    #[\Override]
    public function whereEquals(string $field, string $value): self
    {
        $this->queries[] = "{$field} = '{$value}'";
        return $this;
    }

    /**
     * @param array<int, RecordID> $ids
     */
    #[\Override]
    public function whereByIds(array $ids): self
    {
        $query = 'OR(';
        collect($ids)->each(function (RecordID $id) use (&$query) {
            $query .= "RECORD_ID() = '{$id->value()}',";
        });
        $query = rtrim($query, ',');
        $query .= ')';

        $this->queries[] = $query;
        return $this;
    }

    #[\Override]
    public function whereIn(string $field, array $values): self
    {
        $query = 'OR(';
        collect($values)->each(function ($value) use (&$query, $field) {
            assert(is_string($value));

            $query .= "{$field} = '{$value}',";
        });
        $query = rtrim($query, ',');
        $query .= ')';

        $this->queries[] = $query;
        return $this;
    }

    #[\Override]
    public function whereSameDay(string $field, Date $date): self
    {
        $this->queries[] = "IS_SAME({$field}, '{$date->value()}')";
        return $this;
    }

    #[\Override]
    public function whereBefore(string $field, Date $date): self
    {
        $this->queries[] = "IS_BEFORE({$field}, '{$date->value()}')";
        return $this;
    }

    #[\Override]
    public function whereAfter(string $field, Date $date): self
    {
        $this->queries[] = "IS_AFTER({$field}, '{$date->value()}')";
        return $this;
    }

    #[\Override]
    public function whereBetween(string $field, Date $startDate, Date $endDate): self
    {
        $startDateStr    = $startDate->value();
        $endDateStr      = $endDate->value();
        $this->queries[] = "AND(IS_AFTER({$field}, '{$startDateStr}'), IS_BEFORE({$field}, '{$endDateStr}'))";
        return $this;
    }

    #[\Override]
    public function all(): self
    {
        $this->isAll = true;
        return $this;
    }

    /**
     * @param string $field
     * @param "asc"|"desc" $direction
     * @return Query
     */
    #[\Override]
    public function orderBy(string $field, string $direction): self
    {
        $this->orderByList[] = ['field' => $field, 'type' => $direction];
        return $this;
    }

    #[\Override]
    public function setLimit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    #[\Override]
    public function applyMappingObject(MappingModelAbstract $mappingObject): self
    {
        $this->mappingObject = $mappingObject;
        return $this;
    }

    #[\Override]
    public function mappingObject(): MappingModelAbstract
    {
        return $this->mappingObject;
    }

    #[\Override]
    public function setCacheKey(string $cacheKey): self
    {
        $this->cacheKey = $cacheKey;
        return $this;
    }

    public function getCacheKey(): string
    {
        return $this->cacheKey;
    }

    public function tableId(): TableID
    {
        return $this->mappingObject->tableId();
    }



    public function targetRecordID(): ?RecordID
    {
        return $this->targetRecordID;
    }

    public function limit(): int
    {
        return $this->limit;
    }

    public function isAll(): bool
    {
        return $this->isAll;
    }
}
