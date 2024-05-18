<?php

use PikaJew002\Handrolled\Application\Application;
use PikaJew002\Handrolled\Http\Responses\ViewResponse;

it('gets ViewResponse from container', function() {
    $app = new Application('./tests/artifacts', './tests/artifacts/config');
    $app->boot();
    $response = $app->get(ViewResponse::class);
    $response->use('test-page.twig.html', ['body' => 'this is the body']);

    expect($response)
        ->code->toEqual(200)
        ->body->toEqual("<!DOCTYPE html><html><head></head><body>this is the body</body></html>")
        ->getHeader('Content-type')->toEqual('text/html');
});
