<?php

use PikaJew002\Handrolled\Application\Application;

beforeEach(function() {
    $this->app = new Application('./tests/artifacts', './tests/artifacts/config');
});

it('loads env variables', function () {
    expect($_ENV['APP_NAME'])->toBe('Handrolled')->and(env('APP_NAME'))->toBe('Handrolled');
});

it('loads config variables from env variables', function () {
    expect($this->app->config('app.name'))->toBe('Handrolled');
});

it('sets project path', function() {
    expect($this->app->getProjectPath())->toBe(realpath(getcwd().'/'.$this->app->config('app.paths.project')));
});

it('sets config path', function() {
    expect($this->app->getConfigPath())->toBe(realpath('./tests/artifacts/config'));
});

it('sets routes path', function() {
    expect($this->app->getRoutesPath())->toBe(realpath($this->app->getProjectPath().'/'.$this->app->config('app.paths.routes')));
});

it('sets views path', function() {
    expect($this->app->getViewsPath())->toBe(realpath($this->app->getProjectPath().'/'.$this->app->config('app.paths.views')));
});

it('sets cache path', function() {
    expect($this->app->getCachePath())->toBe(realpath($this->app->getProjectPath().'/'.$this->app->config('app.paths.cache')));
});

it('has services array', function() {
    expect($this->app->getServices())->toBeArray();
});

it('sets static instance', function() {
  expect($this->app)->toBe(Application::getInstance());
});
