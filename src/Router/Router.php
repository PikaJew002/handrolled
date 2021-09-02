<?php

namespace PikaJew002\Handrolled\Router;

use PikaJew002\Handrolled\Http\Request;
use PikaJew002\Handrolled\Interfaces\Container;

class Router
{
    protected Container $container;
    protected Request $request;
    protected Route $route;
    protected array $routeMiddleware;

    public function __construct(Container $container, Request $request, array $routeInfo)
    {
        $this->container = $container;
        $this->request = $request;
        $this->route = new Route($container, ...$routeInfo);
    }

    public function pipeRequestThroughToResponse(array $pipes)
    {
        $resolver = $this->route->getResolver();
        $pipes = array_merge($pipes, $this->route->middleware);

        return (new Pipeline($this->container, $this->request, $pipes))
                ->resolveToResponse(function($request) use ($resolver) {
                    return $resolver($request);
                });
    }
}
