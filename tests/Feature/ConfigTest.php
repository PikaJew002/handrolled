<?php

use PikaJew002\Handrolled\Support\Configuration;

beforeEach(function() {
    $this->config = new Configuration();
});

it('sets config value using set method', function() {
    $this->config->set([
        'foo.all' => 'bar',
    ]);

    expect($this->config->get('foo.all'))->toBe('bar');
});

it('sets config value using getOrSet method', function() {
    $this->config->getOrSet(['foo.all' => 'bar']);

    expect($this->config->getOrSet('foo.all'))->toBe('bar');
});
