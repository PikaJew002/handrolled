<?php

use PikaJew002\Handrolled\Application\Application;
use PikaJew002\Handrolled\Database\Schema\Schema;
use PikaJew002\Handrolled\Database\Schema\Table;

beforeEach(function() {
    $this->app = new Application('./tests/artifacts', './tests/artifacts/config');
    $this->app->config([
        'database.driver' => 'sqlite',
    ]);
    $this->app->boot();
});

it('throws Exception for method call on non-existent method', function() {
    Schema::create('table', function(Table $table) {
        $table->nonMethod();
    });
})->throws(BadMethodCallException::class);
