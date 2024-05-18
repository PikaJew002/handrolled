<?php

use PikaJew002\Handrolled\Http\Exceptions\ResponseHeaderException;
use PikaJew002\Handrolled\Http\Response;

beforeEach(function() {
    $this->response = new Response();
});

it('has, gets, and sets header', function() {
    $this->response->header(['content-type' => 'text/html']);

    expect($this->response->hasHeader('content_type'))->toBeTrue();
    expect($this->response->header('CONTENT_TYPE'))->toBe('text/html');
    expect($this->response->headers['Content-Type'])->toBe('text/html');
});

it('gets and sets code', function () {
    $this->response->setCode(201);

    expect($this->response->getCode())->toBe(201);
});

it('throws Exception if header name being set is empty or only contains whitespace', function() {
    $this->response->setHeader(" \t\n ", 'value');
})->throws(ResponseHeaderException::class);
