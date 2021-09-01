<?php

namespace PikaJew002\Handrolled\Router;

use Closure;
use Exception;
use PikaJew002\Handrolled\Interfaces\Container;
use PikaJew002\Handrolled\Http\Request;
use ReflectionFunction;
use ReflectionMethod;

class Route
{
    public Container $container;
    public array $middleware;
    public $resolver;

    /**
     * $handler param can be array|callable|string
     * $code is not used and only included for array destructuring of $routeInfo array from route dispatch
     */
    public function __construct(Container $container, int $code, $handler, array $params)
    {
        $this->container = $container;
        $this->middleware = [];
        if(is_array($handler)) {
            $this->middleware = isset($handler['middleware']) ? $handler['middleware'] : [];
            if(isset($handler['class'])) {
                if(isset($handler['method'])) {
                    $this->resolver = function($request) use ($handler, $params) {
                        return $this->resolveFromClassMethod($request, $params, $handler['class'], $handler['method']);
                    };
                } else {
                    $this->resolver = function($request) use ($handler, $params) {
                        return $this->resolveFromClassMethod($request, $params, $handler['class'], '__invoke');
                    };
                }
            } else if(isset($handler['closure'])) {
                $this->resolver = function($request) use ($handler, $params) {
                    return $this->resolveFromClosure($request, $params, $handler['closure']);
                };
            } else {
                throw new Exception('Badly formed route. Handler array should have key class or closure.');
            }
        } else if(is_callable($handler)) {
            $this->resolver = function($request) use ($handler, $params) {
                return $this->resolveFromClosure($request, $params, $handler);
            };
        } else if(is_string($handler)) {
            $this->resolver = function($request) use ($handler, $params) {
                return $this->resolveFromClassMethod($request, $params, $handler, '__invoke');
            };
        } else {
            throw new Exception('Badly formated route. Handler should be array or callable or string.');
        }
    }

    public function getResolver()
    {
        return $this->resolver;
    }

    protected function resolveFromClosure(Request $request, array $params, Closure $closure, array $middleware = [])
    {
        $closureParams = $this->getArgs(
            (new ReflectionFunction($closure))->getParameters(),
            $params,
            $request
        );
        return $closure(...$closureParams);
    }

    protected function resolveFromClassMethod(Request $request, array $params, string $controllerClass, string $controllerMethod)
    {
        $methodParams = $this->getArgs(
            (new ReflectionMethod($controllerClass, $controllerMethod))->getParameters(),
            $params,
            $request
        );
        if($controllerMethod === '__invoke') {
            return $this->container->get($controllerClass)(...$methodParams);
        }
        return $this->container->get($controllerClass)->$controllerMethod(...$methodParams);
    }

    protected function getArgs(array $reflectionParams = [], array $params = [], Request $request): array
    {
        $controllerParams = [];
        foreach($reflectionParams as $param) {
            if(array_key_exists($param->getName(), $params)) {
                $controllerParams[] = $params[$param->getName()];
                continue;
            }
            if($param->isDefaultValueAvailable()) {
                $controllerParams[] = $param->getDefaultValue();
                continue;
            }
            if($param->getType()->getName() === $request::class) {
                $controllerParams[] = $request;
                continue;
            }
            $controllerParams[] = $this->container->get($param->getType()->getName());
        }
        return $controllerParams;
    }
}
