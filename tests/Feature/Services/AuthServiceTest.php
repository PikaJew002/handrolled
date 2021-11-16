<?php

use PikaJew002\Handrolled\Application\Application;
use PikaJew002\Handrolled\Application\Services\AuthService;
use PikaJew002\Handrolled\Interfaces\Token;
use PikaJew002\Handrolled\Interfaces\User;

beforeEach(function() {
    $this->app = new Application('./tests/artifacts', './tests/artifacts/config');
    $this->app->bootService(new AuthService($this->app));
});

it('aliases user interface to user class', function() {
  expect($this->app->hasAlias(User::class))->toBeTrue();
  expect($this->app->getAlias(User::class))->toBe($this->app->config('auth.user'));
});

it('aliases token interface to token class', function() {
    expect($this->app->hasAlias(Token::class))->toBeTrue();
    expect($this->app->getAlias(Token::class))->toBe($this->app->config('auth.drivers.token.class'));
});
