<?php

namespace PikaJew002\Handrolled\Router\Definition;

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
        return $this->addRoute('GET', $uri, $handler);
    }

    public function post(string $uri, $handler): self
    {
        return $this->addRoute('POST', $uri, $handler);
    }

    public function put(string $uri, $handler): self
    {
        return $this->addRoute('PUT', $uri, $handler);
    }

    public function patch(string $uri, $handler): self
    {
        return $this->addRoute('PATCH', $uri, $handler);
    }

    public function delete(string $uri, $handler): self
    {
        return $this->addRoute('DELETE', $uri, $handler);
    }

    public function addGroup(string $prefix, callable $callback): self
    {
        $this->lastDefined = [];
        $finalPrefix = $this->prefix . $prefix;
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

    public function middleware($middleware): void
    {
        if(!empty($this->lastDefined)) {
            $middleware = is_string($middleware) ? [$middleware] : $middleware;
            foreach($this->lastDefined as $lastDefinition) {
                foreach($this->definitions as $key => $definition) {
                    if($definition === $lastDefinition) {
                        $this->definitions[$key] = $lastDefinition->addMiddleware($middleware);
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
