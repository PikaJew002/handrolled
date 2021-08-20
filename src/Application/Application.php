<?php

namespace PikaJew002\Handrolled\Application;

use Dotenv\Dotenv;
use FastRoute\Dispatcher;
use Illuminate\Config\Repository;
use Illuminate\Support\Collection;
use PDO;
use PikaJew002\Handrolled\Container\Container;
use PikaJew002\Handrolled\Http\Request;
use PikaJew002\Handrolled\Http\Response;
use PikaJew002\Handrolled\Http\Responses\MethodNotAllowedResponse;
use PikaJew002\Handrolled\Http\Responses\NotFoundResponse;
use PikaJew002\Handrolled\Interfaces\Container as ContainerInterface;
use PikaJew002\Handrolled\Interfaces\Database as DatabaseInterface;
use PikaJew002\Handrolled\Interfaces\Response as ResponseInterface;
use PikaJew002\Handrolled\Support\Configuration;
use ReflectionFunction;
use ReflectionMethod;

class Application extends Container implements ContainerInterface
{
    protected $routeDispatcher;
    protected Configuration $configBindings;

    public function __construct(array $objectBindings = [], array $aliasBindings = [], array $valueBindings = [], Configuration $configBindings = null)
    {
        static::setInstance($this);
        $this->objectBindings = $objectBindings;
        $this->aliasBindings = $aliasBindings;
        $this->valueBindings = $valueBindings;
        $this->configBindings = $configBindings ?? new Configuration();
    }

    public function handleRequest(): ResponseInterface
    {
        return $this->routeTo($this->get(Request::class));
    }

    protected function routeTo(Request $request): ResponseInterface
    {
        $routeInfo = $this->routeDispatcher->dispatch($request->input('_method', $request->method), $request->uri);
        if($routeInfo[0] == Dispatcher::NOT_FOUND) {
            return new NotFoundResponse();
        }
        if($routeInfo[0] == Dispatcher::METHOD_NOT_ALLOWED) {
            return new MethodNotAllowedResponse($routeInfo[1]);
        }
        $routeParams = $routeInfo[2];
        // if the handler passed is in form of [Controller::class, 'methodName']
        if(is_array($routeInfo[1])) {
            $controllerClass = $routeInfo[1][0];
            $controllerMethod = $routeInfo[1][1];
            $methodParams = $this->getArgs(
                (new ReflectionMethod($controllerClass, $controllerMethod))->getParameters(),
                $routeParams
            );
            return $this->get($controllerClass)->$controllerMethod(...$methodParams);
        }
        if(is_callable($routeInfo[1])) {
            $closure = $routeInfo[1];
            $closureParams = $this->getArgs(
                (new ReflectionFunction($closure))->getParameters(),
                $routeParams
            );
            return $closure(...$closureParams);
        }
        // if the handler passed is an invokable class InvokableController::class
        $controllerClass = $routeInfo[1][0];
        $invokableParams = $this->getArgs(
            (new ReflectionMethod($controllerClass, '__invoke'))->getParameters(),
            $routeParams
        );
        return $this->get($controllerClass)(...$invokableParams);
    }

    protected function getArgs(array $reflectionParams = [], array $routeParams = []): array
    {
        $params = [];
        foreach($reflectionParams as $param) {
            if(array_key_exists($param->getName(), $routeParams)) {
                $params[] = $routeParams[$param->getName()];
                continue;
            }
            if($param->isDefaultValueAvailable()) {
                $params[] = $param->getDefaultValue();
                continue;
            }
            $params[] = $this->get($param->getType()->getName());
        }
        return $params;
    }

    // just a shortcut to get to config repo
    public function config($input)
    {
        return $this->configBindings->getOrSet($input);
    }

    public function bootConfig(string $envPath, string $configPath)
    {
        $dotenv = Dotenv::createImmutable($envPath);
        $dotenv->load();
        $configFiles = (new Collection(scandir($configPath)))->reject(function($file) use ($configPath) {
            return $file === '.' || $file === '..' || (! file_exists($configPath .'/'. $file));
        })->mapWithKeys(function($fileName) use ($configPath) {
            return [explode('.', $fileName)[0] => require($configPath .'/'. $fileName)];
        })->all();
        $this->config($configFiles);
    }

    public function bootRoutes(string $routesPath)
    {
        $this->routeDispatcher = require $routesPath;
    }

    public function bootDatabase(string $driver = 'mysql')
    {
        $dbConfig = $this->config("database.$driver");
        $this->set($dbConfig['class'], function(ContainerInterface $c) use ($driver) {
              $class = $c->config("database.$driver.class");
              return new $class(
                $c->config("database.$driver.host"),
                $c->config("database.$driver.database"),
                $c->config("database.$driver.username"),
                $c->config("database.$driver.password")
            );
        });
        $this->setAlias(DatabaseInterface::class, $dbConfig['class']);
    }
}
