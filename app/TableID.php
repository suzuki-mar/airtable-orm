<?php

declare(strict_types=1);

namespace App\GenericLibrary\AirTable;

final readonly class TableID
{
    private string $tableId;

    public function __construct(string $tableId)
    {
        $this->tableId = $tableId;
        if (! $this->isValid()) {
            throw new \InvalidArgumentException('Invalid Table ID');
        }
    }

    public function value(): string
    {
        return $this->tableId;
    }

    private function isValid(): bool
    {
        return preg_match('/^tbl[a-zA-Z0-9]{14}$/', $this->tableId) === 1;
    }
}
