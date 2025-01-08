<?php

declare(strict_types=1);

namespace App\GenericLibrary\AirTable\Internal\TypeDetector;

use App\GenericLibrary\AirTable\Field\Date;
use App\GenericLibrary\AirTable\Field\DateTime;
use App\GenericLibrary\AirTable\Field\LongText;
use App\GenericLibrary\AirTable\Field\StringLine;
use App\GenericLibrary\AirTable\Field\Type;

readonly class FromString
{
    # メソッドの行数が長くても分けた方がわかりづらくなるのでこのメソッドで完結をしたい
    // phpcs:ignore SlevomatCodingStandard.Functions.FunctionLength
    public static function execute(string $value): Type
    {
        if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return Type::Email;
        }

        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return Type::URL;
        }

        if (self::isTelephone($value)) {
            return Type::Telephone;
        }

        if (DateTime::isValid($value)) {
            return Type::DateTime;
        }

        if (Date::isValid($value)) {
            return Type::Date;
        }

        if (LongText::isValid($value)) {
            return Type::LongText;
        }

        if (StringLine::isValid($value)) {
            return Type::SingleLine;
        }

        throw new \InvalidArgumentException("Invalid Field Type value {$value}");
    }

    private static function isTelephone(string $value): bool
    {
        $digits = preg_replace('/\D/', '', $value);

        if (! is_string($digits)) {
            throw new \InvalidArgumentException('Invalid Telephone');
        }

        $patterns = [
            'US/Canada'          => '/^1\d{10}$/',
            'Japanese Mobile'    => '/^0[789]0\d{8}$/',
            'Japanese Landline'  => '/^0[1-9]{1,4}\d{6,8}$/',
            'Japanese Toll-Free' => '/^0120\d{6}$/',
        ];

        foreach ($patterns as $_ => $pattern) {
            if (preg_match($pattern, $digits)) {
                return true;
            }
        }

        return false;
    }
}
