<?php

use Doctrine\DBAL\Connection;
use PikaJew002\Handrolled\Application\Application;
use PikaJew002\Handrolled\Application\Services\DatabaseService;
use PikaJew002\Handrolled\Database\Exceptions\DatabaseDriverException;

beforeEach(function() {
    $this->app = new Application('./tests/artifacts', './tests/artifacts/config');
    $this->app->bootDatabase();
});

it('throws exception if database driver not supported', function() {
    $this->app->config(['database.driver' => 'nonexistantdriver']);
    (new DatabaseService($this->app))->boot();
})->throws(DatabaseDriverException::class);

it('supports mysql driver', function() {
    $this->app->config(['database.driver' => 'mysql']);
    (new DatabaseService($this->app))->boot();

    expect($this->app->hasFactory(Connection::class))->toBeTrue();
    expect($this->app->hasSingleton(Connection::class))->toBeTrue();
});

it('supports sqlite driver', function() {
    $this->app->config(['database.driver' => 'sqlite']);
    (new DatabaseService($this->app))->boot();

    expect($this->app->hasFactory(Connection::class))->toBeTrue();
    expect($this->app->hasSingleton(Connection::class))->toBeTrue();
});
