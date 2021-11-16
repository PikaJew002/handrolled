<?php

use PikaJew002\Handrolled\Container\Container;
use PikaJew002\Handrolled\Support\Configuration;

beforeEach(function() {
    $this->container = new Container();
});

afterEach(function() {
    $this->container->flush();
});

it('gets instance', function() {
    $instance = $this->container->get(Configuration::class);

    expect($instance)->toBeInstanceOf(Configuration::class);
});

it('gets instance using factory', function() {
    $this->container->set(Configuration::class, function($app) {
        return new \DateTime();
    });
    $instance = $this->container->get(Configuration::class);

    expect($instance)->toBeInstanceOf(\DateTime::class);
});

it('sets factory', function() {
    $this->container->set(Configuration::class, function($app) {
        return new \DateTime();
    });

    expect($this->container->hasFactory(Configuration::class))->toBeTrue();
});

it('registers singleton', function() {
    $this->container->registerSingletons(Configuration::class);

    expect($this->container->hasSingleton(Configuration::class))->toBeTrue();
});

it('gets singleton instance', function() {
    $this->container->registerSingletons(Configuration::class);
    $instance = $this->container->get(Configuration::class);
    $instance2 = $this->container->get(Configuration::class);

    expect($instance)->toBe($instance2);
});

it('sets alias', function() {
    $this->container->setAlias('config', Configuration::class);

    expect($this->container->hasAlias('config'))->toBeTrue();
});

it('gets alias', function() {
    $this->container->setAlias('config', Configuration::class);

    expect($this->container->getAlias('config'))->toBe(Configuration::class);
});
