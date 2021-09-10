<?php

namespace PikaJew002\Handrolled\Router;

use FastRoute\RouteCollector as FastRouteCollector;
use FastRoute\Dispatcher;
use function FastRoute\simpleDispatcher;
use PikaJew002\Handrolled\Http\Request;
use PikaJew002\Handrolled\Interfaces\Container;
use PikaJew002\Handrolled\Router\Definition\RouteCollector;
use PikaJew002\Handrolled\Router\Definition\RouteGroup;

class Router
{
    protected Container $container;
    protected Request $request;
    protected Route $route;

    public function __construct(Container $container, Request $request, array $routeInfo)
    {
        $this->container = $container;
        $this->request = $request;
        $this->route = new Route($container, ...$routeInfo);
    }

    public function pipeRequestThroughToResponse(array $pipes)
    {
        $resolver = $this->route->resolver;
        $pipes = array_merge($pipes, $this->route->middleware);

        return (new Pipeline($this->container, $this->request, $pipes))
                ->resolveToResponse(function($request) use ($resolver) {
                    return $resolver($request);
                });
    }

    public static function processRoutes(RouteCollector $routeCollector): Dispatcher
    {
        return simpleDispatcher(function(FastRouteCollector $r) use ($routeCollector) {
            foreach($routeCollector->getDefinitions() as $definition) {
                if($definition instanceof RouteGroup) {
                    static::addRoutes($definition, $r, $routeCollector);
                } else {
                    $r->addRoute($definition->method, $definition->uri, $definition->handler);
                }
            }
        });
    }

    protected static function addRoutes($routeGroup, FastRouteCollector $r, RouteCollector $routeCollector): void
    {
        foreach($routeGroup->getDefinitions() as $definition) {
            if($definition instanceof RouteGroup) {
                static::addRoutes($definition, $r, $routeCollector);
            } else {
                $r->addRoute($definition->method, $definition->uri, $definition->handler);
            }
        }
    }
}
