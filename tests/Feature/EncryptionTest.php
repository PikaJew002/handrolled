<?php

use PikaJew002\Handrolled\Application\Application;
use PikaJew002\Handrolled\Auth\Encryption;
use PikaJew002\Handrolled\Support\Configuration;

beforeEach(function() {
    $this->app = new Application('./tests/artifacts', './tests/artifacts/config');
    $this->encryption = $this->app->get(Encryption::class);
});

it('receives Configuration from container', function() {
    $config = $this->app->get(Configuration::class);

    expect($config)->toBe($this->encryption->config);
});

it('encrypts and decrypts string', function() {
    $encryptedString = $this->encryption->encrypt('password');

    expect($this->encryption->decrypt($encryptedString))->toBe('password');
});

it('throws exception if key is not right length', function () {
    $this->app->config(['app.encryption_key' =>'superdupersecretkeybutwronglength']);
    $encryptedString = $this->encryption->encrypt('password');

    $this->encryption->decrypt($encryptedString);
})->throws(SodiumException::class);
