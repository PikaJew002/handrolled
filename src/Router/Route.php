<?php

namespace PikaJew002\Handrolled\Router;

use Closure;
use Exception;
use PikaJew002\Handrolled\Interfaces\Container;
use PikaJew002\Handrolled\Interfaces\Response;
use PikaJew002\Handrolled\Http\Request;
use PikaJew002\Handrolled\Http\Responses\ViewResponse;
use ReflectionFunction;
use ReflectionMethod;

class Route
{
    protected Container $container;
    public array $middleware;
    public Closure $resolver;

    public function __construct(Container $container, int $code, $handler, array $params)
    {
        $this->container = $container;
        $this->middleware = $handler['middleware'];
        if(!is_null($handler['class']) && !is_null($handler['method'])) {
            $this->resolver = function(Request $request) use ($handler, $params) {
                return $this->resolveFromClassMethod($request, $params, $handler['class'], $handler['method']);
            };
        } else if(!is_null($handler['closure'])) {
            $this->resolver = function(Request $request) use ($handler, $params) {
                return $this->resolveFromClosure($request, $params, $handler['closure']);
            };
        } else {
            throw new Exception('Badly formed route. Handler array should have key class or closure.');
        }
    }

    protected function resolveFromClosure(Request $request, array $params, Closure $closure): Response
    {
        $closureParams = $this->getArgs((new ReflectionFunction($closure))->getParameters(), $params, $request);
        $response = $closure(...$closureParams);
        if($response instanceof ViewResponse) {
            $response = $response->buildFromContainer($this->container);
        }

        return $response;
    }

    protected function resolveFromClassMethod(Request $request, array $params, string $class, string $method): Response
    {
        $methodParams = $this->getArgs((new ReflectionMethod($class, $method))->getParameters(), $params, $request);
        $response = $method === '__invoke'
                    ? $this->container->get($class)(...$methodParams)
                    : $this->container->get($class)->$method(...$methodParams);
        if($response instanceof ViewResponse) {
            $response = $response->buildFromContainer($this->container);
        }

        return $response;
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
            if($param->getType()->getName() === get_class($request)) {
                $controllerParams[] = $request;
                continue;
            }
            $controllerParams[] = $this->container->get($param->getType()->getName());
        }

        return $controllerParams;
    }
}
