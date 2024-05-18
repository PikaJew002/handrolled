<?php

use PikaJew002\Handrolled\Application\Application;
use PikaJew002\Handrolled\Application\Exceptions\PathDefinitionException;
use PikaJew002\Handrolled\Container;
use PikaJew002\Handrolled\Http\Request;
use PikaJew002\Handrolled\Http\Response;
use PikaJew002\Handrolled\Http\Responses\ExceptionResponse;
use PikaJew002\Handrolled\Http\Responses\HttpErrors;
use PikaJew002\Handrolled\Interfaces\Container as ContainerInterface;

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

it('boots other logic', function() {
    $this->app->boot(function($app) {
        $app->set(Request::class, fn($app) => Request::mock('/unique-uri', 'HEAD'));
    });

    $request = $this->app->get(Request::class);

    expect($request->getUri())->toBe('/unique-uri');
    expect($request->getMethod())->toBe('HEAD');
});

it('catches Exception in boot logic, renders debug page', function() {
    $this->app->config(['app.debug' => true, 'app.response_type' => 'text/html']);
    ob_start();
    $this->app->boot(function($app) {
        $app->set(Request::class, fn($app) => Request::mock(''));
        throw new Exception('Custom Exception');
    });
    $output = ob_get_clean();

    expect($output)->toContainString('Custom Exception');
});

it('catches Exception in boot logic, renders server error page', function () {
    $this->app->config(['app.debug' => false, 'app.response_type' => 'text/html']);
    ob_start();
    $this->app->boot(function ($app) {
        $app->set(Request::class, fn ($app) => Request::mock(''));
        throw new Exception('Custom Exception');
    });
    $output = ob_get_clean();

    expect($output)->toContainString('Server Error');
});

it('handles request and returns response', function() {
    $this->app->boot();
    $request = new Request('/', 'GET');
    $response = $this->app->handleRequest($request);

    expect($response)->toBeInstanceOf(Response::class);
});

it('handles request from container and returns response', function () {
    $this->app->boot(function($app) {
        $app->set(Request::class, fn($app) => Request::mock('/', 'GET'));
    });

    $response = $this->app->handleRequest();

    expect($response)->toBeInstanceOf(Response::class);
});

it('handles request and returns NotFoundResponse (HTTP 404)', function() {
    $this->app->boot();
    $request = new Request('/nothere', 'GET');
    $response = $this->app->handleRequest($request);

    expect($response)->toBeInstanceOf(HttpErrors\NotFoundResponse::class);
});

it('handles request and returns MethodNotAllowedResponse (HTTP 405)', function() {
    $this->app->boot();
    $request = new Request('/', 'POST');
    $response = $this->app->handleRequest($request);

    expect($response)->toBeInstanceOf(HttpErrors\MethodNotAllowedResponse::class);
    expect($response->hasHeader('Allow'))->toBeTrue();
    expect($response->getHeader('Allow'))->toBe('GET');
});

it('handles request, catches HttpException (500) and returns ServerErrorResponse', function() {
    $this->app->boot();
    $request = new Request('/exception500', 'GET');
    $response = $this->app->handleRequest($request);

    expect($response)->toBeInstanceOf(HttpErrors\ServerErrorResponse::class);
});

it('handles request, catches HttpException (400) and returns BadRequestResponse', function() {
    $this->app->boot();
    $request = new Request('/exception400', 'GET');
    $response = $this->app->handleRequest($request);

    expect($response)->toBeInstanceOf(HttpErrors\BadRequestResponse::class);
});

it('handles request, catches HttpException (401) and returns UnauthorizedResponse', function() {
    $this->app->boot();
    $request = new Request('/exception401', 'GET');
    $response = $this->app->handleRequest($request);

    expect($response)->toBeInstanceOf(HttpErrors\UnauthorizedResponse::class);
});

it('handles request, catches HttpException (403) and returns ForbiddenResponse', function() {
    $this->app->boot();
    $request = new Request('/exception403', 'GET');
    $response = $this->app->handleRequest($request);

    expect($response)->toBeInstanceOf(HttpErrors\ForbiddenResponse::class);
});

it('handles request, catches HttpException (408) and returns RequestTimeoutResponse', function() {
    $this->app->boot();
    $request = new Request('/exception408', 'GET');
    $response = $this->app->handleRequest($request);

    expect($response)->toBeInstanceOf(HttpErrors\RequestTimeoutResponse::class);
});

it('handles request, catches Exception and returns ServerErrorResponse', function() {
    $this->app->boot();
    $request = new Request('/exceptiongeneric', 'GET');
    $response = $this->app->handleRequest($request);

    expect($response)->toBeInstanceOf(HttpErrors\ServerErrorResponse::class);
});

it('handles request, catches Exception and returns ExceptionResponse', function () {
    $this->app->config(['app.debug' => true]);
    $this->app->boot();
    $request = new Request('/exceptiongeneric', 'GET');
    $response = $this->app->handleRequest($request);

    expect($response)->toBeInstanceOf(ExceptionResponse::class);
});

it('throws Exception on invalid path', function() {
    $this->app = new Application('./tests/artifacts', './tests/artifacts/config2');
})->throws(PathDefinitionException::class);
