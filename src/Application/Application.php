<?php

namespace PikaJew002\Handrolled\Application;

use Dotenv\Dotenv;
use FastRoute\Dispatcher;
use Illuminate\Config\Repository;
use Illuminate\Support\Collection;
use PDO;
use PikaJew002\Handrolled\Container\Container;
use PikaJew002\Handrolled\Database\Implementations\MySQL;
use PikaJew002\Handrolled\Database\Implementations\PostgreSQL;
use PikaJew002\Handrolled\Http\Request;
use PikaJew002\Handrolled\Http\Response;
use PikaJew002\Handrolled\Http\Responses\MethodNotAllowedResponse;
use PikaJew002\Handrolled\Http\Responses\NotFoundResponse;
use PikaJew002\Handrolled\Interfaces\Container as ContainerInterface;
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
        return $this->routeTo($this->get('request'));
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

    protected function getArgs(array $reflectionParams, array $routeParams): array
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
        $this->setAlias('request', Request::class);
        $this->routeDispatcher = require $routesPath;
    }

    public function bootDatabase($driver = 'mysql', $abstract = '\PDO', $alias = 'db')
    {
        //die(var_dump($this->config('database')));
        $this->set($abstract, function(ContainerInterface $c) {
            if($dbConfig = $c->config('database.mysql')) {
                return new MySQL(
                    $dbConfig['host'],
                    $dbConfig['database'],
                    $dbConfig['username'],
                    $dbConfig['password']
                );
            } elseif($dbConfig = $c->config('database.pgsql')) {
                return new PostgreSQL(
                    $dbConfig['host'],
                    $dbConfig['database'],
                    $dbConfig['username'],
                    $dbConfig['password']
                );
            } else {
                throw new \Exception('Database config not found! Please provide a valid database configuration!');
            }
        });
        $this->setAlias($alias, $abstract);
    }
}
