<?php

declare(strict_types=1);

namespace App\GenericLibrary\AirTable\Field;

use App\GenericLibrary\AirTable\FieldValue;

abstract class StringAbstract implements FieldValue
{
    protected string $value;

    /**
     * @param string $value
     */
    public function __construct(string $value)
    {
        $this->value = $value;
    }

    #[\Override]
    public function value(): string
    {
        return $this->value;
    }

    abstract public static function isValid(string $value): bool;
}
