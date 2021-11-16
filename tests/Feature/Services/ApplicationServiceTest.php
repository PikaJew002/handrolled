<?php

use PikaJew002\Handrolled\Application\Application;
use PikaJew002\Handrolled\Application\Services\ApplicationService;
use PikaJew002\Handrolled\Container\Container;
use PikaJew002\Handrolled\Http\Request;
use PikaJew002\Handrolled\Interfaces\Container as ContainerInterface;

beforeEach(function() {
    $this->app = new Application('./tests/artifacts', './tests/artifacts/config');
    $this->app->bootService(new ApplicationService($this->app));
});

it('registers application singletons', function() {
    expect($this->app->hasSingleton(Application::class))->toBeTrue();
});

it('registers request singletons', function() {
    expect($this->app->hasSingleton(Request::class))->toBeTrue();
});

it('registers application factory', function() {
    expect($this->app->hasFactory(Application::class))->toBeTrue();
});

it('registers request factory', function() {
    expect($this->app->hasFactory(Request::class))->toBeTrue();
});

it('aliases container class to application class', function() {
    expect($this->app->hasAlias(Container::class))->toBeTrue();
    expect($this->app->getAlias(Container::class))->toBe(Application::class);
});

it('aliases container interface to application class', function() {
    expect($this->app->hasAlias(ContainerInterface::class))->toBeTrue();
    expect($this->app->getAlias(ContainerInterface::class))->toBe(Application::class);
});
