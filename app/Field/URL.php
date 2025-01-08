<?php

declare(strict_types=1);

namespace App\GenericLibrary\AirTable\Field;

use App\GenericLibrary\AirTable\FieldValue;
use GuzzleHttp\Psr7\Uri;

final readonly class URL implements FieldValue
{
    private Uri $value;

    public function __construct(string $valueStr)
    {
        $url = new Uri($valueStr);

        $this->value = $url;
    }

    #[\Override]
    public function value(): string
    {
        return (string)$this->value;
    }
}
