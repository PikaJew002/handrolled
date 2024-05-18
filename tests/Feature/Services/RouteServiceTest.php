<?php

use FastRoute\Dispatcher;
use FastRoute\Dispatcher\GroupCountBased;
use PikaJew002\Handrolled\Application\Application;

beforeEach(function() {
    $this->app = new Application('./tests/artifacts', './tests/artifacts/config');
    $this->app->bootRoutes();
});

it('has route dispatcher', function() {
    expect($this->app->get(Dispatcher::class))->toBeInstanceOf(GroupCountBased::class);
});
