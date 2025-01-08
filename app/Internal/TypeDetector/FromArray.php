<?php

declare(strict_types=1);

namespace App\GenericLibrary\AirTable\Internal\TypeDetector;

use App\GenericLibrary\AirTable\Field\RecordID;
use App\GenericLibrary\AirTable\Field\Type;
use Webmozart\Assert\Assert;

readonly class FromArray
{
    /**
     * @param array<int, RecordID|string>|array<string, mixed> $values
     */
    public static function execute(array $values): Type
    {
        if (! array_is_list($values)) {
            if (self::isRequiredKeys($values, ['text', 'type'])) {
                return Type::Barcode;
            }
        }

        /** @var non-empty-string $valuesStr */
        $valuesStr = json_encode($values);

        Assert::notEmpty($values, $valuesStr);
        Assert::true(array_is_list($values), "values : {$valuesStr}");

        if (self::isAttachmentList($values)) {
            return Type::AttachmentList;
        } elseif (RecordID::isValid($values[0])) {
            return Type::RelatedRecordIDs;
        } else {
            return Type::MultipleSelects;
        }
    }

    /**
     * @param list<mixed> $values
     */
    private static function isAttachmentList(array $values): bool
    {
        //        添付ファイルの場合は一番め以降も同じ構造になっている
        $value = $values[0];

        if (! is_array($value)) {
            return false;
        }

        if (array_is_list($value)) {
            return false;
        }

        return self::isRequiredKeys($value, ['url', 'filename', 'size', 'type']);
    }


    /**
     * @param array<string|int, mixed> $values
     * @param array<int, string> $requiredKeys
     */
    private static function isRequiredKeys(array $values, array $requiredKeys): bool
    {
        foreach (array_keys($values) as $key) {
            if (! is_string($key)) {
                throw new \InvalidArgumentException('Array keys must all be strings.');
            }
        }

        $currentKeys = array_keys($values);
        $missingKeys = array_diff($requiredKeys, $currentKeys);
        return count($missingKeys) === 0;
    }
}
