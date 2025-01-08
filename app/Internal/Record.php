<?php

declare(strict_types=1);

namespace App\GenericLibrary\AirTable\Internal;

use App\GenericLibrary\AirTable\Field\AttachmentList;
use App\GenericLibrary\AirTable\Field\Date;
use App\GenericLibrary\AirTable\Field\DateTime;
use App\GenericLibrary\AirTable\Field\LongText;
use App\GenericLibrary\AirTable\Field\Number;
use App\GenericLibrary\AirTable\Field\RecordID;
use App\GenericLibrary\AirTable\Field\StringLine;
use App\GenericLibrary\AirTable\Field\Type;
use App\GenericLibrary\AirTable\Field\URL;
// phpcs:ignore SlevomatCodingStandard.Namespaces.UnusedUses
use App\GenericLibrary\AirTable\FieldValue;
use InvalidArgumentException;
use Webmozart\Assert\Assert;

//  テスト用に継承をできるようにして扱いやすくしている
final readonly class Record
{
    public RecordID $id;
    public DateTime $createdTime;

    /**
     * @var array<string, FieldValue| AttachmentList | array<int, FieldValue>>
     */
    public array $fields;

    /**
     * @param RecordID $id
     * @param DateTime $createdTime
     * @param array<string, FieldValue|AttachmentList|array<int, FieldValue>> $fields
     *
     *                          /
     */
    public function __construct(RecordID $id, DateTime $createdTime, array $fields)
    {
        $this->id          = $id;
        $this->createdTime = $createdTime;
        $this->fields      = $fields;
    }

    public static function buildFromJSONRecord(JsonRecord $record): Record
    {
        $id          = new RecordID($record->id);
        $createdTime = new DateTime($record->createdTime);
        $fields      = self::buildFields($record->fields);

        return new Record($id, $createdTime, $fields);
    }

    //    TODO フィールドの配列を複数箇所で使い回すのでクラスを作成する
    /**
     * @return array<string, AttachmentList|FieldValue|array<int, FieldValue>>
     * @param non-empty-array<string, array<int, mixed>|bool|float|int|string> $fieldValues
     */
    private static function buildFields(array $fieldValues): array
    {
        $fields = [];
        foreach ($fieldValues as $key => $value) {
            $type = Type::fromValue($value);

            $field = self::buildFiledFromType($type, $value);

            if ($field !== null) {
                $fields[$key] = $field;
                continue;
            }

            $valueStr = json_encode($value);
            assert(is_string($valueStr));
            throw new InvalidArgumentException("Invalid Field Value error: {$key} value {$valueStr}");
        }
        return $fields;
    }

    /**
     * @param Type $type
     * @param array<int, mixed>|bool|float|int|string $value
     * @return FieldValue|array<int, FieldValue>|AttachmentList|null
     */
    private static function buildFiledFromType(Type $type, mixed $value): FieldValue | array | AttachmentList | null
    {
        if ($type === Type::AttachmentList) {
            Assert::true(is_array($value) && array_is_list($value), 'To be an associative array');
            return AttachmentList::buildFromArrayValues($value);
        }

        if ($type === Type::Number) {
            Assert::true(
                is_int($value) || is_float($value) || is_string($value),
                'Number value must be int, float, or string.'
            );
            return new Number($value);
        }

        if (is_array($value)) {
            return self::convertFromArray($value);
        }

        if (is_string($value)) {
            return self::convertFromString($type, $value);
        }

        return null;
    }

    private static function convertFromString(Type $type, string $value): FieldValue
    {
        return match ($type) {
            Type::DateTime   => new DateTime($value),
            Type::Date       => new Date($value),
            Type::SingleLine => new StringLine($value),
            Type::URL        => new URL($value),
            Type::LongText   => new LongText($value),
            default          => throw new \InvalidArgumentException("Invalid Type: type {$type->toString()}"),
        };
    }

    /**
     * @param array<int, mixed> $values
     * @return array<int, FieldValue>
     */
    private static function convertFromArray(array $values): array
    {
        $type        = Type::fromValue($values);

        $isAllString = collect($values)->every(fn ($value) => is_string($value));

        if (! $isAllString) {
            $message = json_encode($values);
            throw new \InvalidArgumentException("Invalid Field Value : {$message}");
        }

        $type        = Type::fromValue($values);
        $filedValues = [];
        foreach ($values as $value) {
            assert(is_string($value));

            $filedValues[] = match ($type) {
                Type::RelatedRecordIDs => new RecordID($value),
                Type::MultipleSelects  => new StringLine($value),
                default                => throw new \InvalidArgumentException(
                    "Invalid Type : type {$type->toString()} value {$value}"
                ),
            };
        }

        return $filedValues;
    }
}
