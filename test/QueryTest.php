<?php

use App\GenericLibrary\AirTable\Internal\Query;
use App\GenericLibrary\AirTable\Parameter;

test('曖昧検索のQueryを組み立てられる', function () {
    $query = new Query();
    $query->whereContains('Name', '美桜');
    $param = new Parameter($query);
    expect($param->filterByFormula)->toBe("SEARCH('美桜', {Name})");
});
