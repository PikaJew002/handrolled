<?php

namespace PikaJew002\Handrolled\Router;

use FastRoute\RouteCollector as FastRouteCollector;
use FastRoute\Dispatcher;
use function FastRoute\simpleDispatcher;
use PikaJew002\Handrolled\Http\Request;
use PikaJew002\Handrolled\Interfaces\Container;
use PikaJew002\Handrolled\Interfaces\Response;
use PikaJew002\Handrolled\Router\Definition\RouteGroup;

class Router
{
    protected Container $container;
    protected Request $request;
    protected Route $route;

    public function __construct(Container $container, Request $request, array $handler, array $params)
    {
        $this->container = $container;
        $this->request = $request;
        $this->route = new Route($container, $handler, $params);
    }

    public function pipeRequestThroughToResponse(): Response
    {
        $resolver = $this->route->resolver;

        return (new Pipeline($this->container, $this->request, $this->route->middleware))
                ->resolveToResponse(function($request) use ($resolver) {
                    return $resolver($request);
                });
    }

    public static function processRoutes(RouteGroup $routeCollector): Dispatcher
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

    protected static function addRoutes($routeGroup, FastRouteCollector $r, RouteGroup $routeCollector): void
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
