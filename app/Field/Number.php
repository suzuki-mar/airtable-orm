<?php

declare(strict_types=1);

namespace App\GenericLibrary\AirTable\Field;

use App\GenericLibrary\AirTable\FieldValue;

final readonly class Number implements FieldValue
{
    private int|float $value;

    public function __construct(string|int|float $value)
    {
        if (is_string($value)) {
            $value = (int) $value;
        }

        $this->value = $value;
    }

    #[\Override]
    public function value(): string
    {
        return (string)$this->value;
    }
}
