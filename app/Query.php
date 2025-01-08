<?php

namespace App\GenericLibrary\AirTable;

use App\GenericLibrary\AirTable\Field\Date;
use App\GenericLibrary\AirTable\Field\RecordID;

interface Query
{
    public function whereContains(string $field, string $value): self;
    public function whereBy(RecordID $recordID): self;
    public function whereEquals(string $field, string $value): self;
    /**
     * @param array<int, RecordID> $ids
     */
    public function whereByIds(array $ids): self;

    /**
     * @param array<int, mixed> $values
     */
    public function whereIn(string $field, array $values): self;

    public function whereSameDay(string $field, Date $date): self;
    public function whereBefore(string $field, Date $date): self;
    public function whereAfter(string $field, Date $date): self;
    public function whereBetween(string $field, Date $startDate, Date $endDate): self;
    public function all(): self;


    /**
     * @param string $field
     * @param "asc"|"desc" $direction
     * @return Query
     */
    public function orderBy(string $field, string $direction): self;
    public function setLimit(int $limit): self;

    public function applyMappingObject(MappingModelAbstract $mappingObject): self;

    public function mappingObject(): MappingModelAbstract;

    public function setCacheKey(string $cacheKey): self;
}
