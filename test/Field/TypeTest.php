<?php

use App\GenericLibrary\AirTable\Field\Type;
use Tests\GenericLibrary\AirTable\Faker;

describe('タイプの判定', function () {
    test('外部RecordIDの判定ができること', function () {
        $type = Type::fromValue([Faker::recordId(), Faker::recordId()]);
        expect($type)->toBe(Type::RelatedRecordIDs);
    });

    test('複数選択の判定ができること', function () {
        $type = Type::fromValue([Faker::singleLine(), Faker::singleLine()]);
        expect($type)->toBe(Type::MultipleSelects);
    });

    test('文字列の判定ができること', function () {
        $type = Type::fromValue(Faker::singleLine());
        expect($type)->toBe(Type::SingleLine);
    });

    test('数値の判定ができること', function () {
        $type = Type::fromValue('1234');
        expect($type)->toBe(Type::Number);
    });

    test('日時の判定ができること', function () {
        $type = Type::fromValue('2024-10-25T10:30:00.000Z');
        expect($type)->toBe(Type::DateTime);
    });

    test('長い文字列の判定ができること', function () {
        $type = Type::fromValue(Faker::longText());
        expect($type)->toBe(Type::LongText);
    });

    test('日付の判定ができること', function () {
        $type = Type::fromValue('2024-10-25');
        expect($type)->toBe(Type::Date);
    });

    test('Emailの判定ができること', function () {
        $type = Type::fromValue('example@example.com');
        expect($type)->toBe(Type::Email);
    });

    test('電話番号の判定ができること', function () {
        $type = Type::fromValue('+1-123-456-7890');
        expect($type)->toBe(Type::Telephone);
    });

    test('URLの判定ができること', function () {
        $type = Type::fromValue('https://example.com');
        expect($type)->toBe(Type::URL);
    });

    test('チェックボックスの判定ができること', function () {
        $type = Type::fromValue(true);
        expect($type)->toBe(Type::Checkbox);
    });

    test('小数点付き数の判定ができること', function () {
        $type = Type::fromValue(0.99);
        expect($type)->toBe(Type::Number);
    });

    test('整数の判定ができること', function () {
        $type = Type::fromValue(1);
        expect($type)->toBe(Type::Number);
    });

    test('バーコードの判定ができること', function () {
        $type = Type::fromValue([
            'text' => '123456789012',
            'type' => 'code128',
        ]);
        expect($type)->toBe(Type::Barcode);
    });

    test('画像の添付ファイルの判定ができること', function () {
        $value = [
            Faker::imageFiled(),
        ];
        $type = Type::fromValue($value);

        expect($type)->toBe(Type::AttachmentList);
    });
});
