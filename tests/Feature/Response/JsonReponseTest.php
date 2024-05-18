<?php

use PikaJew002\Handrolled\Application\Application;
use PikaJew002\Handrolled\Http\Responses\JsonResponse;

it('makes JsonResponse from static constructor', function() {
    $response = JsonResponse::make([
        'key' => 'value'
    ]);

    // var_dump($response->getBody());

    expect($response)
        ->code->toEqual(200)
        ->body->toEqual(json_encode(['key' => 'value']))
        ->getHeader('Content-type')->toEqual('application/json')
        ->getHeader('Cache-Control')->toEqual('no-cache, private');
});

it('gets JsonResponse from container', function() {
    $response = (new Application('./tests/artifacts', './tests/artifacts/config'))->get(JsonResponse::class);
    $response = $response->with([
        'other_key' => 'other_value'
    ]);

    expect($response)
        ->code->toEqual(200)
        ->body->toEqual(json_encode(['other_key' => 'other_value']))
        ->getHeader('Content-type')->toEqual('application/json')
        ->getHeader('Cache-Control')->toEqual('no-cache, private');
});
