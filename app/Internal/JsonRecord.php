<?php

declare(strict_types=1);

namespace App\GenericLibrary\AirTable\Internal;

use InvalidArgumentException;

//　Arrayで扱うとデータ構造が複雑なのでDTO的なクラスとして作成している
final class JsonRecord
{
    public string $id;
    public string $createdTime;

    /**
     * @var non-empty-array<string, array<int, mixed>|bool|float|int|string>
     */
    public array $fields;

    /**
     * @param string $id
     * @param string $createdTime
     * @param non-empty-array<string, array<int, mixed>|bool|float|int|string> $fields
     */
    public function __construct(string $id, string $createdTime, array $fields)
    {
        $this->id          = $id;
        $this->createdTime = $createdTime;
        $this->fields      = $fields;
    }

    /**
     * Build an array of JsonRecord instances from raw records.
     *
     * @param array<int, array<string, mixed>> $recordsArray
     * @return array<int, self>
     */
    public static function buildFromRecords(array $recordsArray): array
    {
        $records = [];

        foreach ($recordsArray as $record) {
            $records[] = self::buildFromRecord($record);
        }

        return $records;
    }

    /**
     * Build a single JsonRecord instance from a raw record.
     *
     * @param array<string, mixed> $record
     * @return self
     */
    private static function buildFromRecord(array $record): self
    {
        self::assertRecord($record);
        /** @var array<string, mixed> $fields */
        $fields           = $record['fields'];
        $normalizedFields = self::normalizeFields($fields);

        /** @var string $id */
        $id = $record['id'];
        /** @var string $createdTime */
        $createdTime = $record['createdTime'];

        return new self(
            $id,
            $createdTime,
            $normalizedFields
        );
    }

    /**
     * @param array<string, mixed> $record
     * @throws InvalidArgumentException
     */
    private static function assertRecord(array $record): void
    {
        if (! isset($record['id']) || ! is_string($record['id'])) {
            throw new InvalidArgumentException('Invalid record: Missing or invalid "id".');
        }

        if (! isset($record['createdTime']) || ! is_string($record['createdTime'])) {
            throw new InvalidArgumentException('Invalid record: Missing or invalid "createdTime".');
        }

        if (! is_array($record['fields']) || empty($record['fields'])) {
            throw new InvalidArgumentException('Invalid record: Missing or invalid "fields".');
        }

        $keys              = array_keys($record['fields']);
        $allKeysAreStrings = true;

        foreach ($keys as $key) {
            if (! is_string($key)) {
                $allKeysAreStrings = false;
                break;
            }
        }

        if (! $allKeysAreStrings) {
            throw new InvalidArgumentException('Invalid record: Field keys must be strings.');
        }
    }

    /**
     * Normalize the fields array.
     *
     * @param array<string, mixed> $fields
     * @return non-empty-array<string, array<int, mixed>|bool|float|int|string>
     */
    private static function normalizeFields(array $fields): array
    {
        $normalizedFields = [];

        foreach ($fields as $key => $value) {
            if (is_array($value)) {
                $normalizedFields[$key] = array_values($value); // Convert associative arrays to indexed arrays
            } elseif (is_bool($value) || is_int($value) || is_float($value) || is_string($value)) {
                $normalizedFields[$key] = $value;
            } else {
                throw new InvalidArgumentException("Invalid field value type for key '{$key}'.");
            }
        }

        if (empty($normalizedFields)) {
            throw new InvalidArgumentException('Fields must not be empty.');
        }

        return $normalizedFields;
    }
}
