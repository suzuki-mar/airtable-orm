<?php

declare(strict_types=1);

namespace App\GenericLibrary\AirTable\Field;

use DateTime as BuildInDateTime;
use App\GenericLibrary\AirTable\FieldValue;

abstract class DateAbstract implements FieldValue
{
    abstract public static function formattedFormat(): string;
    abstract public static function parseFormat(): string;

    private readonly BuildInDateTime $value;

    public function __construct(string $valueStr)
    {
        $value = static::buildDateTime($valueStr);

        if ($value === false) {
            throw new \InvalidArgumentException("Invalid Date Format: {$valueStr}");
        }

        $this->value = $value;
    }

    #[\Override]
    public function value(): string
    {
        return $this->value->format(static::formattedFormat());
    }

    public static function isValid(mixed $value): bool
    {
        if (! is_string($value)) {
            return false;
        }

        return self::buildDateTime($value) !== false;
    }

    protected static function buildDateTime(string $value): BuildInDateTime | false
    {
        return BuildInDateTime::createFromFormat(static::parseFormat(), $value);
    }
}
