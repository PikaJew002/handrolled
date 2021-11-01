<?php

use PikaJew002\Handrolled\Http\Request;

beforeEach(function() {
    $this->request = new Request(
        '', // uri
        'GET', // method
        ['REQUEST_URI' => ''], // server
        ['ACCEPT' => 'application/json', 'CONTENT_TYPE' => 'application/json'], // headers
        ['post_key' => 'post_value'], // request
        ['get_key' => 'get_value'], // query
        ['cookie_key' => 'cookie_value'], // cookies
        ['file_key' => 'file_value'], // files
        '' // body
    );
});

it('sets uri', function() {
    expect($this->request->getUri())->toBeEmpty();
});

it('sets method', function() {
    expect($this->request->getMethod())->toBe(Request::HTTP_GET);
});

it('sets server', function() {
    expect($this->request->getServer())->toBe(['REQUEST_URI' => '']);
});

it('sets headers', function() {
    expect($this->request->hasHeader('Accept'))->toBeTrue();
    expect($this->request->getHeader('Accept'))->toBe('application/json');
    expect($this->request->hasHeader('Content-Type'))->toBeTrue();
    expect($this->request->getHeader('Content-Type'))->toBe('application/json');
    expect($this->request->getHeaders())->toBe([
        'ACCEPT' => 'application/json',
        'CONTENT_TYPE' => 'application/json']
    );
});

it('sets request', function() {
    expect($this->request->hasRequest('post_key'))->toBeTrue();
    expect($this->request->request('post_key'))->toBe('post_value');
    expect($this->request->input('post_key'))->toBe('post_value');
    expect($this->request->getRequest())->toBe(['post_key' => 'post_value']);
});

it('sets query', function() {
    expect($this->request->hasQuery('get_key'))->toBeTrue();
    expect($this->request->input('get_key'))->toBe('get_value');
    expect($this->request->query('get_key'))->toBe('get_value');
    expect($this->request->getQuery())->toBe(['get_key' => 'get_value']);
});

it('sets cookies', function() {
    expect($this->request->hasCookie('cookie_key'))->toBeTrue();
    expect($this->request->getCookie('cookie_key'))->toBe('cookie_value');
    expect($this->request->getCookies())->toBe(['cookie_key' => 'cookie_value']);
});

it('sets files', function() {
    expect($this->request->getFiles())->toBe(['file_key' => 'file_value']);
});

it('sets body', function() {
    expect($this->request->getBody())->toBeEmpty();
});
