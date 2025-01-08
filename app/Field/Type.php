<?php

declare(strict_types=1);

namespace App\GenericLibrary\AirTable\Field;

use App\GenericLibrary\AirTable\Internal\TypeDetector\FromArray;
use App\GenericLibrary\AirTable\Internal\TypeDetector\FromString;

enum Type: string
{
    case SingleLine           = 'SingleLine';
    case Number               = 'Number';
    case DateTime             = 'DateTime';
    case Date                 = 'Date';
    case RelatedRecordIDs     = 'RelatedRecordIDs';
    case MultipleSelects      = 'MultipleSelects';
    case LongText             = 'LongText';
    case Email                = 'Email';
    case AttachmentList       = 'AttachmentList';
    //    通常のAttachmentとはデータ構造が違う
    case Telephone        = 'Telephone';
    case URL              = 'URL';


    case Percent = 'Percent';

    case Checkbox = 'Checkbox';

    case Barcode = 'Barcode';

    /**
     * @param array<int, mixed>|string|bool $value
     */
    public static function fromValue(array|string|bool|int|float $value): self
    {
        if (is_numeric($value)) {
            return self::Number;
        }

        if (is_bool($value)) {
            return self::Checkbox;
        }

        if (is_string($value)) {
            return FromString::execute($value);
        }

        return FromArray::execute($value);
    }

    public function toString(): string
    {
        return $this->value;
    }
}
