<?php

declare(strict_types=1);

namespace App\GenericLibrary\AirTable\Field;

use App\GenericLibrary\AirTable\Field\Attachment\Image;
use InvalidArgumentException;
use Webmozart\Assert\Assert;

class AttachmentList
{
    /**
     * @var Image[]
     */
    public private(set) array $items;
    protected string $value;

    /**
     * @param Image[] $items
     */
    public function __construct(array $items)
    {
        $this->items = $items;
    }

    /**
     * @param array<int, mixed> $values
     */
    public static function buildFromArrayValues(array $values): self
    {
        $items = [];

        foreach ($values as $value) {
            if (! is_array($value)) {
                throw new \InvalidArgumentException('Invalid AttachmentList');
            }

            Assert::keyExists($value, 'type', 'Invalid AttachmentList');
            assert(is_string($value['type']));

            if (str_starts_with($value['type'], 'image')) {
                Assert::isMap($value, 'The value must be an associative array.');
                //                こっちでアサーションをしているのでPHPStanでのチェックはしない
                self::assertValidImageData($value);
                /** @phpstan-ignore-next-line */
                $items[] = new Image($value);
            } else {
                throw new InvalidArgumentException("Unsupported AttachmentList type {$value['type']}");
            }
        }

        return new self($items);
    }


    /**
     * 指定のデータ構造を持つ配列かどうかを確認するメソッド
     *
     * @param array<string, mixed> $value
     * @throws \InvalidArgumentException
     */
    // メソッドの行数が長くても分けた方がわかりづらくなるのでこのメソッドで完結をしたい
    // phpcs:ignore SlevomatCodingStandard.Functions.FunctionLength
    private static function assertValidImageData(array $value): void
    {
        Assert::keyExists($value, 'url', 'The "url" key is missing.');
        Assert::string($value['url'], 'The "url" key must be a string.');

        Assert::keyExists($value, 'thumbnails', 'The "thumbnails" key is missing.');
        Assert::isArray($value['thumbnails'], 'The "thumbnails" key must be an array.');

        foreach (['small', 'large', 'full'] as $size) {
            Assert::keyExists(
                $value['thumbnails'],
                $size,
                "The \"{$size}\" key is missing in \"thumbnails\"."
            );
            Assert::isArray(
                $value['thumbnails'][$size],
                "The \"{$size}\" key in \"thumbnails\" must be an array."
            );
            Assert::keyExists(
                $value['thumbnails'][$size],
                'url',
                "The \"url\" key is missing in \"thumbnails.{$size}\"."
            );
            Assert::string(
                $value['thumbnails'][$size]['url'],
                "The \"url\" in \"thumbnails.{$size}\" must be a string."
            );
        }
    }
}
