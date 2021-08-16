<?php

namespace PikaJew002\Handrolled\Application;

use PikaJew002\Handrolled\Container\Container;
use PikaJew002\Handrolled\Http\Request;
use PikaJew002\Handrolled\Http\Response;
use PikaJew002\Handrolled\Http\Responses\NotFoundResponse;
use PikaJew002\Handrolled\Http\Responses\MethodNotAllowedResponse;
use PikaJew002\Handrolled\Interfaces\Container as ContainerInterface;
use PikaJew002\Handrolled\Interfaces\Response as ResponseInterface;
use PikaJew002\Handrolled\Support\Configuration;
use Dotenv\Dotenv;
use FastRoute;
use Illuminate\Config\Repository;
use Illuminate\Support\Collection;
use PDO;

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
        return $this->routeTo($this->get('request'));
    }

    protected function routeTo(Request $request): ResponseInterface
    {
        $routeInfo = $this->routeDispatcher->dispatch($request->method, $request->uri);
        if($routeInfo[0] == FastRoute\Dispatcher::NOT_FOUND) {
            return new NotFoundResponse();
        }
        if($routeInfo[0] == FastRoute\Dispatcher::METHOD_NOT_ALLOWED) {
            return new MethodNotAllowedResponse($routeInfo[1]);
        }
        // if the handler passed is in form of [Controller::class, 'methodName']
        if(is_array($routeInfo[1])) {
            $controllerInstance = $this->get($routeInfo[1][0]);
            $controllerMethod = $routeInfo[1][1];
            $controllerMethodArgs = array_values($routeInfo[2]);
            return $controllerInstance->$controllerMethod(...$controllerMethodArgs);
        }
        // if the handler passed is an invokable class InvokableController::class
        $controllerInstance = $this->get($routeInfo[1][0]);
        $controllerMethodArgs = array_values($routeInfo[2]);
        return $controllerInstance(...$controllerMethodArgs);
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
        $this->setAlias('request', Request::class);
        $this->routeDispatcher = require $routesPath;
    }

    public function bootDatabase($driver = 'mysql', $abstract = '\PDO', $alias = 'db')
    {
        $this->set($abstract, function(ContainerInterface $c) {
            if($dbConfig = $c->config('database.mysql')) {
                return new \PDO(
                    'mysql:host='.$dbConfig['host'].';dbname='.$dbConfig['dbname'],
                    $dbConfig['username'],
                    $dbConfig['password']
                );
            } elseif($dbConfig = $c->config('database.pgsql')) {
                return new \PDO(
                    'pgsql:host='.$dbConfig['host'].';port='.$dbConfig['port'].';dbname='.$dbConfig['dbname'].';user='.$dbConfig['username'].';password='.$dbConfig['password']
                );
            } else {
                throw new \Exception('PostgreSQl config not found! Please provide a valid database configuration!');
            }
        });
        $this->setAlias($alias, $abstract);
    }
}
