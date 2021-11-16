<?php

use PikaJew002\Handrolled\Support\Configuration;

beforeEach(function() {
    $this->config = new Configuration();
});

it('sets config value', function() {
    $this->config->set([
        'foo' => 'bar',
    ]);

    expect($this->config->get('foo'))->toBe('bar');
});
