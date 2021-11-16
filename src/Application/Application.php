<?php

namespace PikaJew002\Handrolled\Application;

use Dotenv\Dotenv;
use FastRoute\Dispatcher;
use Illuminate\Support\Collection;
use PikaJew002\Handrolled\Application\Services;
use PikaJew002\Handrolled\Container\Container;
use PikaJew002\Handrolled\Http\Exceptions\HttpException;
use PikaJew002\Handrolled\Http\Request;
use PikaJew002\Handrolled\Http\Responses\ExceptionHtmlResponse;
use PikaJew002\Handrolled\Http\Responses\HttpErrors;
use PikaJew002\Handrolled\Interfaces\Container as ContainerInterface;
use PikaJew002\Handrolled\Interfaces\Response as ResponseInterface;
use PikaJew002\Handrolled\Interfaces\Service as ServiceInterface;
use PikaJew002\Handrolled\Router\Router;
use PikaJew002\Handrolled\Support\Configuration;
use Throwable;

class Application extends Container implements ContainerInterface
{
    protected string $projectPath;
    protected string $configPath;
    protected string $routesPath;
    protected string $viewsPath;
    protected string $cachePath;
    protected array $services;

    public function __construct(string $envPath = '../', string $configPath = '../config')
    {
        parent::__construct();
        $this->loadEnvFile($envPath);
        $this->loadConfiguration($configPath);
        $this->projectPath = $this->setPath($this->config('app.paths.project'), getcwd());
        $this->configPath = realpath($configPath);
        $this->routesPath = $this->setPath($this->config('app.paths.routes'), $this->projectPath);
        $this->viewsPath = $this->setPath($this->config('app.paths.views'), $this->projectPath);
        $this->cachePath = $this->setPath($this->config('app.paths.cache'), $this->projectPath);
        $this->services = $this->config('app.services', []);
        static::setInstance($this);
    }

    // just a shortcut to get/set from config repo
    public function config($input, $default = null)
    {
        return $this->get(Configuration::class)->getOrSet($input, $default);
    }

    public function envIsProduction(): bool
    {
        return $this->config('app.env') === 'production';
    }

    protected function setPath(string $path, string $base): string
    {
        if($path === '') {
            return realpath($base);
        } else if(strlen($path) > 0 && $this->isRelativePath($path)) {
            return realpath($base . '/' . $path);
        }

        return realpath($path);
    }

    protected function isRelativePath(string $path): bool
    {
        return strncmp($path, '/', 1) !== 0;
    }

    protected function loadEnvFile(string $envPath): void
    {
        Dotenv::createImmutable(realpath($envPath))->load();
    }

    protected function loadConfiguration(string $configPath): void
    {
        $this->registerSingletons([
            Configuration::class,
        ]);
        $configFiles = (new Collection(scandir($configPath)))->reject(function ($file) use ($configPath) {
            return $file === '.' || $file === '..' || (!file_exists($configPath . '/' . $file));
        })->mapWithKeys(function ($fileName) use ($configPath) {
            return [explode('.', $fileName)[0] => require($configPath . '/' . $fileName)];
        })->all();
        $this->config($configFiles);
    }

    public function getProjectPath(): string
    {
        return $this->projectPath;
    }

    public function getConfigPath(): string
    {
        return $this->configPath;
    }

    public function getRoutesPath(): string
    {
        return $this->routesPath;
    }

    public function getViewsPath(): string
    {
        return $this->viewsPath;
    }

    public function getCachePath(): string
    {
        return $this->cachePath;
    }

    public function getServices(): array
    {
        return $this->services;
    }

    public function handleRequest(?Request $request = null): ResponseInterface
    {
        $request = !is_null($request) ? $request : $this->get(Request::class);
        $routeInfo = $this->get(Dispatcher::class)->dispatch($request->input('_method', $request->getMethod()), $request->getUri());
        if($routeInfo[0] == Dispatcher::NOT_FOUND) {
            return new HttpErrors\NotFoundResponse();
        }
        if($routeInfo[0] == Dispatcher::METHOD_NOT_ALLOWED) {
            return new HttpErrors\MethodNotAllowedResponse($routeInfo[1]);
        }
        try {
            $router = new Router($this, $request, $routeInfo);
            return $router->pipeRequestThroughToResponse();
        } catch(HttpException $e) {
            return $this->convertHttpExceptionToResponse($e);
        } catch(Throwable $e) {
            return $this->convertExceptionToResponse($e);
        }
    }

    public function boot(?callable $callback = null): void
    {
        foreach($this->services as $service) {
            $this->bootService(new $service($this));
        }
        $this->bootOther($callback);
    }

    public function bootService(ServiceInterface $serviceClass): void
    {
        try {
            $serviceClass->boot();
        } catch (Throwable $e) {
            $this->convertExceptionToResponse($e)->render();
            exit;
        }
    }

    public function bootApplication(): void
    {
        $this->bootService(new Services\ApplicationService($this));
    }

    public function bootDatabase(): void
    {
        $this->bootService(new Services\DatabaseService($this));
    }

    public function bootAuth(): void
    {
        $this->bootService(new Services\AuthService($this));
    }

    public function bootRoutes(): void
    {
        $this->bootService(new Services\RouteService($this));
    }

    public function bootViews(): void
    {
        $this->bootService(new Services\ViewService($this));
    }

    public function bootOther(?callable $callback = null): void
    {
        if(!is_null($callback)) {
            $callback($this);
        }
    }

    protected function convertHttpExceptionToResponse(HttpException $e): ResponseInterface
    {
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
    }

    protected function convertExceptionToResponse(Throwable $e): ResponseInterface
    {
        if($this->config('app.debug', false) === true) {
            return new ExceptionHtmlResponse($e);
        }

        return new HttpErrors\ServerErrorResponse();
    }
}
