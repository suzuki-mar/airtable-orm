<?php

declare(strict_types=1);

namespace App\GenericLibrary\AirTable\Field;

final class StringLine extends StringAbstract
{
    public static function isValid(string $value): bool
    {
        return strlen($value) <= 255;
    }
}
