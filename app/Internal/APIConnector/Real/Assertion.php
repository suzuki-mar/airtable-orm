<?php

namespace App\GenericLibrary\AirTable\Internal\APIConnector\Real;

use InvalidArgumentException;

// APIの接続とは関係ない部分のコードが大きくなったためアサーションはこのクラスに移譲するようにした
final readonly class Assertion
{
    /**
     * @param mixed $responseData
     * @throws InvalidArgumentException
     */
    public static function assertionResponseData(mixed $responseData): void
    {
        if (! is_array($responseData)) {
            throw new InvalidArgumentException('Invalid response: Data must be an array.');
        }

        if (! isset($responseData['records']) && ! isset($responseData['id'])) {
            throw new InvalidArgumentException('Invalid response: Missing "records" or "id" field.');
        }

        if (isset($responseData['records']) && ! is_iterable($responseData['records'])) {
            throw new InvalidArgumentException('"records" must be iterable.');
        }

        foreach ($responseData as $key => $value) {
            if (! is_string($key)) {
                throw new InvalidArgumentException('Invalid response key: Expected a string.');
            }
        }
    }
}
