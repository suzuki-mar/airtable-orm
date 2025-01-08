<?php

namespace App\GenericLibrary\AirTable\TestHelper;

use App\GenericLibrary\AirTable\Field\Attachment\Image;
use App\GenericLibrary\AirTable\Field\AttachmentList;
use App\GenericLibrary\AirTable\Field\Date;
use App\GenericLibrary\AirTable\Field\DateTime;
use App\GenericLibrary\AirTable\Field\LongText;
use App\GenericLibrary\AirTable\Field\RecordID;
use Faker\Factory;
use Faker\Generator;

final readonly class FiledFaker
{
    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create();
    }

    public function id(): RecordID
    {
        $value = 'rec' . $this->faker->regexify('[a-zA-Z0-9]{14}');
        return new RecordID($value);
    }

    public function dateTime(): DateTime
    {
        $value = $this->faker->dateTime()->format(DateTime::parseFormat());
        return new DateTime($value);
    }

    public function date(): Date
    {
        $value = $this->faker->date();
        return new Date($value);
    }

    public function longText(): LongText
    {
        $value = '';
        while (true) {
            $text = $this->faker->text(100000);

            if (LongText::isValid($text)) {
                $value = $text;
                break; // 条件を満たした時点でループを中断
            }
        }

        return new LongText($value);
    }


    public function imageAttachmentList(): AttachmentList
    {
        $imageFields = [
            'url'        => $this->faker->imageUrl(), // ランダムな画像URL
            'thumbnails' => [
                'small' => ['url' => $this->faker->imageUrl(150, 150)],
                'large' => ['url' => $this->faker->imageUrl(800, 600)],
                'full'  => ['url' => $this->faker->imageUrl(1920, 1080)],
            ],
            'type' => 'image/jpeg', // 固定の画像タイプ（必要ならランダム化可能）
        ];

        $image = new Image($imageFields);

        return new AttachmentList([$image]);
    }
}
