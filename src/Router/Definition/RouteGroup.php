<?php

namespace PikaJew002\Handrolled\Router\Definition;

use Closure;
use PikaJew002\Handrolled\Application\Application;

class RouteGroup
{
    protected string $prefix;
    // contains routes and route groups in the order they are registered
    protected array $definitions;
    protected array $lastDefined;

    public function __construct(string $prefix = '')
    {
        $this->prefix = $prefix;
        $this->definitions = [];
        $this->lastDefined = [];
    }

    public function addRoute($methods, string $uri, $handler): self
    {
        return $this->route($methods, $uri, $handler);
    }

    public function route($methods, string $uri, $handler): self
    {
        $this->lastDefined = [];
        $finalUri = $this->prefix . $uri;
        foreach((array) $methods as $method) {
            $route = new Route($method, $finalUri, $handler);
            $this->definitions[] = $route;
            $this->lastDefined[] = $route;
        }

        return $this;
    }

    public function get(string $uri, $handler): self
    {
        return $this->route('GET', $uri, $handler);
    }

    public function post(string $uri, $handler): self
    {
        return $this->route('POST', $uri, $handler);
    }

    public function put(string $uri, $handler): self
    {
        return $this->route('PUT', $uri, $handler);
    }

    public function patch(string $uri, $handler): self
    {
        return $this->route('PATCH', $uri, $handler);
    }

    public function delete(string $uri, $handler): self
    {
        return $this->route('DELETE', $uri, $handler);
    }

    public function addGroup($prefix, callable $callback): self
    {
        return $this->group($prefix, $callback);
    }

    public function group($prefix, callable $callback): self
    {
        $this->lastDefined = [];
        $callback = $prefix instanceof Closure ? $prefix : $callback;
        $finalPrefix = is_string($prefix) ? $this->prefix . $prefix : $this->prefix;
        $group = new self($finalPrefix);
        $callback($group);
        $this->definitions[] = $group;
        $this->lastDefined[] = $group;

        return $this;
    }

    public function addExistingGroup(self $routeGroup): self
    {
        $this->lastDefined = [];
        $this->definitions[] = $routeGroup;
        $this->lastDefined[] = $routeGroup;

        return $this;
    }

    public function middleware(...$middlewares): void
    {
        if(!empty($this->lastDefined)) {
            $finalMiddleware = [];
            foreach($middlewares as $middleware) {
                if(is_string($middleware)) {
                    $middlewareGroups = Application::getInstance()->config('route.middleware', []);
                    $finalMiddleware = array_merge(
                        $finalMiddleware,
                        array_key_exists($middleware, $middlewareGroups) ? $middlewareGroups[$middleware] : [$middleware]
                    );
                } else {
                    $finalMiddleware = array_merge($finalMiddleware, $middleware);
                }
            }
            // to ensure a middleware is not applied more than once
            $finalMiddleware = array_unique($finalMiddleware);
            foreach($this->lastDefined as $lastDefinition) {
                foreach($this->definitions as $key => $definition) {
                    if($definition === $lastDefinition) {
                        $this->definitions[$key] = $lastDefinition->addMiddleware($finalMiddleware);
                    }
                }
            }
        }
        $this->lastDefined = [];
    }

    public function addMiddleware(array $middleware): self
    {
        foreach($this->definitions as $key => $definition) {
            $this->definitions[$key] = $definition->addMiddleware($middleware);
        }

        return $this;
    }

    public function getDefinitions(): array
    {
        return $this->definitions;
    }
}
