<?php

declare(strict_types=1);

namespace App\GenericLibrary\AirTable\Field;

use App\GenericLibrary\AirTable\FieldValue;

final class DateTime extends DateAbstract implements FieldValue
{
    public static function formattedFormat(): string
    {
        return 'Y-m-d H:i:s';
    }

    public static function parseFormat(): string
    {
        return "Y-m-d\TH:i:s.v\Z";
    }
}
