<?php

declare(strict_types=1);

namespace App\GenericLibrary\AirTable\Field;

use App\GenericLibrary\AirTable\FieldValue;

final class Date extends DateAbstract implements FieldValue
{
    public static function parseFormat(): string
    {
        return 'Y-m-d';
    }

    public static function formattedFormat(): string
    {
        return self::parseFormat();
    }
}
