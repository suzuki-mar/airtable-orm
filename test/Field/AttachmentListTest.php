<?php

use App\GenericLibrary\AirTable\Field\AttachmentList;
use Tests\GenericLibrary\AirTable\Faker;

describe('添付ファイル', function () {
    test('画像の添付ファイルを作成できること', function () {
        $value = [
            Faker::imageFiled(),
        ];
        $attachmentList = new AttachmentList($value);

        expect($attachmentList)->toBeInstanceOf(AttachmentList::class);
    });
});
