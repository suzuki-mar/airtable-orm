<?php

declare(strict_types=1);

namespace App\GenericLibrary\AirTable\Field;

use App\GenericLibrary\AirTable\FieldValue;

final class RecordID implements FieldValue
{
    private readonly string $id;

    public function __construct(string $id)
    {
        $this->id = $id;

        if (! self::isValid($id)) {
            throw new \InvalidArgumentException('Invalid Record ID');
        }
    }

    #[\Override]
    public function value(): string
    {
        return $this->id;
    }

    //    Newをしたくないが値があっているかを確認したいというユースケースが存在する
    public static function isValid(mixed $value): bool
    {
        if (! is_string($value)) {
            return false;
        }

        return preg_match('/^rec[a-zA-Z0-9]{14}$/', $value) === 1;
    }

    public function equals(RecordID $recordID): bool
    {
        return $this->id === $recordID->id;
    }
}
