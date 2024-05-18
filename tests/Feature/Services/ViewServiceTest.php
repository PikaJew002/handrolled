<?php

use PikaJew002\Handrolled\Application\Application;
use PikaJew002\Handrolled\Application\Services\ViewService;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Loader\LoaderInterface;

beforeEach(function() {
    $this->app = new Application('./tests/artifacts', './tests/artifacts/config');
    $this->app->bootViews();
});

it('registers twig environment singleton', function() {
    expect($this->app->hasSingleton(Environment::class))->toBeTrue();
});

it('registers twig filesystem loader singleton', function() {
    expect($this->app->hasSingleton(FilesystemLoader::class))->toBeTrue();
});

it('aliases twig loader interface to twig filesystem loader class', function() {
    expect($this->app->hasAlias(LoaderInterface::class))->toBeTrue();
    expect($this->app->getAlias(LoaderInterface::class))->toBe(FilesystemLoader::class);
});

it('sets twig filesystem loader paths', function() {
    expect($this->app->get(FilesystemLoader::class)->getPaths())->toContain($this->app->getViewsPath());
});

it('sets twig environment cache in production', function() {
    $this->app->config(['app.env' => 'production']);
    (new ViewService($this->app))->boot();

    expect($this->app->get(Environment::class)->getCache())->toBe($this->app->getCachePath());
});

it('sets twig environment to debug when not in production', function() {
    $this->app->config(['app.env' => 'local']);
    (new ViewService($this->app))->boot();

    expect($this->app->get(Environment::class)->isDebug())->toBeTrue();
});
