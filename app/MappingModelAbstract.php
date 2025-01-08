<?php

declare(strict_types=1);

namespace App\GenericLibrary\AirTable;

use App\GenericLibrary\AirTable\Field\DateTime;
use App\GenericLibrary\AirTable\Field\RecordID;
use App\GenericLibrary\AirTable\Internal\Record;

abstract class MappingModelAbstract
{
    abstract public function assignValuesForSpecificModel(Record $record): void;
    abstract public function tableId(): TableID;

    private RecordID $id;
    private DateTime $createdTime;

    public function assignValues(Record $record): void
    {
        $this->id          = $record->id;
        $this->createdTime = $record->createdTime;
        $this->assignValuesForSpecificModel($record);
    }

    public function id(): RecordID
    {
        return $this->id;
    }

    public function createdTime(): DateTime
    {
        return $this->createdTime;
    }
}
