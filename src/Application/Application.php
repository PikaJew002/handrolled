<?php

namespace PikaJew002\Handrolled\Application;

use Dotenv\Dotenv;
use Exception;
use FastRoute\Dispatcher;
use Illuminate\Config\Repository;
use Illuminate\Support\Collection;
use PDO;
use PikaJew002\Handrolled\Container\Container;
use PikaJew002\Handrolled\Database\Orm\Exceptions\DatabaseDriverException;
use PikaJew002\Handrolled\Http\Exceptions\HttpException;
use PikaJew002\Handrolled\Http\Request;
use PikaJew002\Handrolled\Http\Response;
use PikaJew002\Handrolled\Http\Responses\ExceptionHtmlResponse;
use PikaJew002\Handrolled\Http\Responses\HttpErrors;
use PikaJew002\Handrolled\Interfaces\Container as ContainerInterface;
use PikaJew002\Handrolled\Interfaces\Database as DatabaseInterface;
use PikaJew002\Handrolled\Interfaces\Response as ResponseInterface;
use PikaJew002\Handrolled\Interfaces\User as UserInterface;
use PikaJew002\Handrolled\Router\Router;
use PikaJew002\Handrolled\Support\Configuration;
use ReflectionFunction;
use ReflectionMethod;
use Throwable;

class Application extends Container implements ContainerInterface
{
    public ?Throwable $exception = null;
    protected $routeDispatcher;
    protected string $projectPath;
    protected string $envPath;
    protected string $configPath;
    protected array $objectBindings;
    protected array $aliasBindings;
    protected array $valueBindings;
    protected Configuration $configBindings;

    public function __construct(
        string $projectPath = '../',
        array $objectBindings = [],
        array $aliasBindings = [],
        array $valueBindings = [],
        Configuration $configBindings = null
    )
    {
        if($projectPath === '') {
            $projectPath = getcwd();
        } else if(strlen($projectPath >= 1) && strncmp($projectPath, '/', 1) !== 0) {
            $projectPath = getcwd().'/'.$projectPath;
        }
        $this->projectPath = realpath($projectPath);
        $this->objectBindings = [];
        $this->aliasBindings = [];
        $this->valueBindings = [];
        $this->configBindings = $configBindings ?? new Configuration();

        $this->set(self::class, fn(self $app) => $app);
        $this->set(Configuration::class, fn(self $app) => $app->getConfigBindings());
        static::setInstance($this);
    }

    public function handleRequest(): ResponseInterface
    {
        return $this->routeTo($this->get(Request::class));
    }

    protected function routeTo(Request $request): ResponseInterface
    {
        $routeInfo = $this->routeDispatcher->dispatch($request->input('_method', $request->method), $request->uri);
        if($routeInfo[0] == Dispatcher::NOT_FOUND) {
            return new HttpErrors\NotFoundResponse();
        }
        if($routeInfo[0] == Dispatcher::METHOD_NOT_ALLOWED) {
            return new HttpErrors\MethodNotAllowedResponse($routeInfo[1]);
        }
        try {
            $router = new Router($this, $request, $routeInfo);
            return $router->pipeRequestThroughToResponse($this->config('route.middleware'));
        } catch(HttpException $e) {
            if($e->httpCode === 400) {
                return new HttpErrors\BadRequestResponse($e->errorMessage);
            }
            if($e->httpCode === 401) {
                return new HttpErrors\UnauthorizedResponse($e->errorMessage);
            }
            if($e->httpCode === 403) {
                return new HttpErrors\ForbiddenResponse($e->errorMessage);
            }
            if($e->httpCode === 408) {
                return new HttpErrors\RequestTimeoutResponse($e->errorMessage);
            }
            return new HttpErrors\ServerErrorResponse($e->errorMessage);
        } catch(Throwable $e) {
            return $this->convertExceptionToResponse($e);
        }
    }

    // just a shortcut to get/set from config repo
    public function config($input)
    {
        return $this->configBindings->getOrSet($input);
    }

    // just a shortcut to get return config repo itself
    public function getConfigBindings()
    {
        return $this->configBindings;
    }

    public function getProjectPath(): string
    {
        return $this->projectPath;
    }

    public function setEnvPath(string $envPath): void
    {
        $this->envPath = $envPath;
    }

    public function setConfigPath(string $configPath): void
    {
        $this->configPath = $configPath;
    }

    public function bootConfig(string $envPath = '', string $configPath = 'config'): void
    {
        if($envPath === '') {
            $envPath = $this->projectPath;
        } else if(strlen($envPath) >= 1 && strncmp($envPath, '/', 1) !== 0) {
            $envPath = $this->projectPath.'/'.$envPath;
        }
        $this->envPath = realpath($envPath);
        $dotenv = Dotenv::createImmutable($envPath);
        $dotenv->load();
        if($configPath === '') {
            $configPath = $this->projectPath;
        } else if(strlen($configPath) >= 1 && strncmp($configPath, '/', 1) !== 0) {
            $configPath = $this->projectPath.'/'.$configPath;
        }
        $this->configPath = realpath($configPath);
        $configFiles = (new Collection(scandir($configPath)))->reject(function($file) use ($configPath) {
            return $file === '.' || $file === '..' || (! file_exists($configPath .'/'. $file));
        })->mapWithKeys(function($fileName) use ($configPath) {
            return [explode('.', $fileName)[0] => require($configPath .'/'. $fileName)];
        })->all();
        $this->config($configFiles);
    }

    public function bootRoutes(string $routesPath = 'routes/api.php'): void
    {
        if($routesPath === '') {
            $routesPath = $this->projectPath;
        } else if(strlen($routesPath) >= 1 && strncmp($routesPath, '/', 1) !== 0) {
            $routesPath = $this->projectPath.'/'.$routesPath;
        }
        $this->routeDispatcher = require realpath($routesPath);
    }

    public function bootDatabase(): void
    {
        if(in_array($this->config('database.driver'), array_keys($this->config('database.drivers')))) {
            $driver = $this->config('database.driver');
            $dbConfig = $this->config("database.drivers.{$driver}");
            $this->set($dbConfig['class'], function(ContainerInterface $c) use ($driver) {
                  $class = $c->config("database.drivers.{$driver}.class");
                  return new $class(
                    $c->config("database.drivers.{$driver}.host"),
                    $c->config("database.drivers.{$driver}.database"),
                    $c->config("database.drivers.{$driver}.username"),
                    $c->config("database.drivers.{$driver}.password")
                );
            });
            $this->setAlias(DatabaseInterface::class, $dbConfig['class']);
        } else {
            $this->exception = new DatabaseDriverException($this->config('database.driver'), $this->exception);
        }
    }

    public function bootAuth(): void
    {
        $this->setAlias(UserInterface::class, $this->config('auth.user'));
    }

    public function convertExceptionToResponse(Throwable $e): ResponseInterface
    {
        if($this->config('app.debug') === 'true') {
            return new ExceptionHtmlResponse($e);
        }
        return new HttpErrors\ServerErrorResponse();
    }

    public function hasBootExceptions(): bool
    {
        return !is_null($this->exception);
    }

    public function renderExceptions(): void
    {
        $this->convertExceptionToResponse($this->exception)->render();
    }
}
