<?php

use PikaJew002\Handrolled\Router\Definition\RouteGroup;
use PikaJew002\Handrolled\Http\Exceptions\HttpException;
use PikaJew002\Handrolled\Http\Response;

$route = new RouteGroup();
$route->get('/', function() {
    return new Response();
});

$route->get('/exception500', function() {
    throw new HttpException(500);
});

$route->get('/exception400', function() {
    throw new HttpException(400);
});

$route->get('/exception401', function() {
    throw new HttpException(401);
});

$route->get('/exception403', function() {
    throw new HttpException(403);
});

$route->get('/exception408', function() {
    throw new HttpException(408);
});

$route->get('/exceptiongeneric', function() {
    throw new Exception('Oops');
});

return $route;
