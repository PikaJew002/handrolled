<?php

use PikaJew002\Handrolled\Application\Application;
use PikaJew002\Handrolled\Application\Services\DatabaseService;
use PikaJew002\Handrolled\Database\Orm\Exceptions\DatabaseDriverException;
use PikaJew002\Handrolled\Interfaces\Database;

beforeEach(function() {
    $this->app = new Application('./tests/artifacts', './tests/artifacts/config');
    $this->app->bootService(new DatabaseService($this->app));
    $driver = $this->app->config('database.driver');
    $this->dbClass = $this->app->config("database.drivers.{$driver}.class");
});

it('throws exception if database driver not supported', function() {
    $this->app->config(['database.driver' => 'nonexistantdriver']);

    (new DatabaseService($this->app))->boot();
})->throws(DatabaseDriverException::class);

it('registers database implementation factory', function() {
    expect($this->app->hasFactory($this->dbClass))->toBeTrue();
});

it('aliases database interface to database implementation class', function() {
    expect($this->app->hasAlias(Database::class))->toBeTrue();
    expect($this->app->getAlias(Database::class))->toBe($this->dbClass);
});
