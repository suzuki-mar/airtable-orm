<?php

declare(strict_types=1);

namespace App\GenericLibrary\AirTable\Field;

final class LongText extends StringAbstract
{
    public static function isValid(string $value): bool
    {
        return strlen($value) > 255;
    }
}
