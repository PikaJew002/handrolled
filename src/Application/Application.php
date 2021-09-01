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
use PikaJew002\Handrolled\Http\Responses\UnauthenticatedResponse;
use PikaJew002\Handrolled\Http\Responses\UnauthorizedResponse;
use PikaJew002\Handrolled\Interfaces\Container as ContainerInterface;
use PikaJew002\Handrolled\Interfaces\Database as DatabaseInterface;
use PikaJew002\Handrolled\Interfaces\Response as ResponseInterface;
use PikaJew002\Handrolled\Router\Router;
use PikaJew002\Handrolled\Support\Configuration;
use PikaJew002\Handrolled\Exceptions\HttpException;
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
        $router = new Router($this, $request, $routeInfo);
        try {
            return $router->pipeRequestThroughToResponse($this->config('route.middleware'));
        } catch(HttpException $e) {
            if($e->code === 400) {
                return new UnauthenticatedResponse($e->message);
            }
            if($e->code === 401) {
                return new UnauthenticatedResponse($e->message);
            }
            if($e->code === 403) {
              return new UnauthorizedResponse($e->message);
            }
            throw $e;
        }
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

    public function bootAuth()
    {
        $this->setAlias(UserInterface::class, $this->config('auth.user'));
    }
}
