<?php

use PikaJew002\Handrolled\Application\Application;
use PikaJew002\Handrolled\Http\Responses\RedirectResponse;

beforeEach(function() {
    $this->app = new Application('./tests/artifacts', './tests/artifacts/config');
    $this->response = $this->app->get(RedirectResponse::class);
});

it('redirect to relative URL with leading slash', function() {
    $response = $this->response->to('/relative-url');

    expect($response)
        ->code->toEqual(303)
        ->body->toContainString('Redirecting to')
        ->getHeader('Location')->toEqual($this->app->config('app.url').'/relative-url');
});

it('redirect to relative URL without leading slash', function () {
    $response = $this->response->to('relative-url');

    expect($response)
        ->code->toEqual(303)
        ->body->toContainString('Redirecting to')
        ->getHeader('Location')->toEqual($this->app->config('app.url') . '/relative-url');
});

it('redirect to relative URL (empty)', function () {
    $response = $this->response->to('');

    expect($response)
        ->code->toEqual(303)
        ->body->toContainString('Redirecting to')
        ->getHeader('Location')->toEqual($this->app->config('app.url') . '/');
});

it('redirect to absolute URL', function () {
    $response = $this->response->to('https://google.com');

    expect($response)
        ->code->toEqual(303)
        ->body->toContainString('Redirecting to')
        ->getHeader('Location')->toEqual('https://google.com');
});
