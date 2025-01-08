<?php

declare(strict_types=1);

namespace Tests\GenericLibrary\AirTable;

use App\GenericLibrary\AirTable\Field\Date;
use App\GenericLibrary\AirTable\Internal\Record;
use App\GenericLibrary\AirTable\Internal\JsonRecord;
use DateMalformedStringException;
use DateTime;
use Faker\Factory;
use Faker\Generator;
use Tests\Competition\WrestlerTestData;

//　なぜかFunctionLengthのエラーが出るので設定ファイル側で対応をしている
class Faker
{
    public static function recordId(): string
    {
        return 'rec' . self::faker()->regexify('[a-zA-Z0-9]{14}');
    }

    public static function singleLine(): string
    {
        return self::faker()->text(255);
    }

    public static function longText(): string
    {
        while (true) {
            $text = self::faker()->text(100000);
            if (strlen($text) > 255) {
                return $text;
            }
        }
    }

    /**
     * @return array{
     *     id: string,
     *     width: int,
     *     height: int,
     *     url: string,
     *     filename: string,
     *     size: int,
     *     type: string,
     *     thumbnails: array{
     *         small: array{
     *             url: string,
     *             width: int,
     *             height: int
     *         },
     *         large: array{
     *             url: string,
     *             width: int,
     *             height: int
     *         },
     *         full: array{
     *             url: string,
     *             width: int,
     *             height: int
     *         }
     *     }
     * }
     */

    public static function imageFiled(): array
    {
        return [
            'id'         => 'sampleId',
            'width'      => 100,
            'height'     => 100,
            'url'        => 'https://example.com/image.png',
            'filename'   => 'sample.png',
            'size'       => 1000,
            'type'       => 'image/png',
            'thumbnails' => [
                'small' => [
                    'url'    => 'https://example.com/thumbnail-small.png',
                    'width'  => 36,
                    'height' => 36,
                ],
                'large' => [
                    'url'    => 'https://example.com/thumbnail-large.png',
                    'width'  => 512,
                    'height' => 512,
                ],
                'full' => [
                    'url'    => 'https://example.com/thumbnail-full.png',
                    'width'  => 100,
                    'height' => 100,
                ],
            ],
        ];
    }

    //    #TOOD: ドメインに関係ないテストようのマッピングクラスを作成する
    public static function wrestlerNameRecord(): Record
    {
        $jsonRecord = new JsonRecord(
            WrestlerTestData::RECORD_ID,
            '2021-09-01T00:00:00.000Z',
            ['団体との関係' => '所属',  '名前'     => '桃野美桜']
        );

        return Record::buildFromJSONRecord($jsonRecord);
    }


    public static function previousDay(string $baseDateStr): Date
    {
        try {
            $date = new DateTime($baseDateStr);
        } catch (DateMalformedStringException $e) {
            throw new \InvalidArgumentException('Invalid date string');
        }


        $previousDay = (clone $date)->modify('-1 day');
        return new Date($previousDay->format('Y-m-d'));
    }

    public static function nextDay(string $baseDateStr): Date
    {
        try {
            $date    = new DateTime($baseDateStr);
            $nextDay = (clone $date)->modify('+1 day');
        } catch (DateMalformedStringException $e) {
            throw new \InvalidArgumentException('Invalid date string');
        }
        return new Date($nextDay->format('Y-m-d'));
    }


    private static function faker(): Generator
    {
        return Factory::create();
    }
}
