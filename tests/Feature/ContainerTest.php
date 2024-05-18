<?php

use PikaJew002\Handrolled\Container;
use PikaJew002\Handrolled\Support\Configuration;

beforeEach(function() {
    $this->container = new Container();
});

afterEach(function() {
    $this->container->flush();
});

it('sets instance of self', function() {
    expect(Container::getInstance())->toBe($this->container);
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

it('has singleton instance', function () {
    $this->container->registerSingletons(Configuration::class);
    $instance = $this->container->get(Configuration::class);

    expect($this->container->hasInstance(Configuration::class))->toBe(true);
});


it('sets alias', function() {
    $this->container->setAlias('config', Configuration::class);

    expect($this->container->hasAlias('config'))->toBeTrue();
});

it('gets alias', function() {
    $this->container->setAlias('config', Configuration::class);

    expect($this->container->getAlias('config'))->toBe(Configuration::class);
});

it('builds dependencies with no constructor', function() {
    class ClassWithNoContructor {}
    $object = $this->container->get(ClassWithNoContructor::class);

    expect($object)->toBeInstanceOf(ClassWithNoContructor::class);
});

it('throws Exception if constructor parameter is not type hinted', function() {
    class ClassWithContructor {
        public function __construct($parameter) {}
    }
    $this->container->get(ClassWithContructor::class);
})->throws(Exception::class);
