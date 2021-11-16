<?php

use FastRoute\Dispatcher;
use FastRoute\Dispatcher\GroupCountBased;
use PikaJew002\Handrolled\Application\Application;
use PikaJew002\Handrolled\Application\Services\RouteService;

beforeEach(function() {
    $this->app = new Application('./tests/artifacts', './tests/artifacts/config');
    $this->app->bootService(new RouteService($this->app));
});

it('has route dispatcher', function() {
    expect($this->app->get(Dispatcher::class))->toBeInstanceOf(GroupCountBased::class);
});
