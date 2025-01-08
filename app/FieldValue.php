<?php

declare(strict_types=1);

namespace App\GenericLibrary\AirTable;

interface FieldValue
{
    public function value(): string;
}
