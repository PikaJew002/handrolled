<?php

namespace PikaJew002\Handrolled\Application;

use Dotenv\Dotenv;
use FastRoute\Dispatcher;
use PikaJew002\Handrolled\Application\Exceptions\PathDefinitionException;
use PikaJew002\Handrolled\Application\Services;
use PikaJew002\Handrolled\Container;
use PikaJew002\Handrolled\Http\Exceptions\HttpException;
use PikaJew002\Handrolled\Http\Request;
use PikaJew002\Handrolled\Http\Responses\ExceptionResponse;
use PikaJew002\Handrolled\Http\Responses\HttpErrorResponse;
use PikaJew002\Handrolled\Http\Responses\HttpErrors\BadRequestResponse;
use PikaJew002\Handrolled\Http\Responses\HttpErrors\UnauthorizedResponse;
use PikaJew002\Handrolled\Http\Responses\HttpErrors\ForbiddenResponse;
use PikaJew002\Handrolled\Http\Responses\HttpErrors\MethodNotAllowedResponse;
use PikaJew002\Handrolled\Http\Responses\HttpErrors\NotFoundResponse;
use PikaJew002\Handrolled\Http\Responses\HttpErrors\RequestTimeoutResponse;
use PikaJew002\Handrolled\Http\Responses\HttpErrors\ServerErrorResponse;
use PikaJew002\Handrolled\Interfaces\Container as ContainerInterface;
use PikaJew002\Handrolled\Interfaces\Request as RequestInterface;
use PikaJew002\Handrolled\Interfaces\Response as ResponseInterface;
use PikaJew002\Handrolled\Interfaces\Service as ServiceInterface;
use PikaJew002\Handrolled\Router\Router;
use PikaJew002\Handrolled\Support\Collection;
use PikaJew002\Handrolled\Support\Configuration;
use Throwable;

class Application extends Container implements ContainerInterface
{
    protected string $projectPath;
    protected string $configPath;
    protected string $routesPath;
    protected string $viewsPath;
    protected string $packageViewsPath;
    protected string $cachePath;
    protected array $services;

    public function __construct(string $envPath = '../', string $configPath = '../config', ?string $envFileName = null)
    {
        parent::__construct();
        $this->loadEnvFile($envPath, $envFileName);
        $this->loadConfiguration($configPath);
        $this->projectPath = $this->setPath($this->config('app.paths.project'), getcwd());
        $this->configPath = realpath($configPath);
        $this->routesPath = $this->setPath($this->config('app.paths.routes'), $this->projectPath);
        $this->viewsPath = $this->setPath($this->config('app.paths.views'), $this->projectPath);
        $this->packageViewsPath = $this->setPath($this->config('app.paths.handrolled_views', 'vendor/pikajew002/handrolled/src/Support/views'), $this->projectPath);
        $this->cachePath = $this->setPath($this->config('app.paths.cache'), $this->projectPath);
        $this->services = $this->config('app.services', []);
        $this->loadApplication();
        static::setInstance($this);
    }

    // just a shortcut to get/set from config repo
    public function config($input, $default = null)
    {
        return $this->get(Configuration::class)->getOrSet($input, $default);
    }

    protected function loadApplication()
    {
        $this->registerSingletons([
            self::class,
            Request::class,
        ]);
        $this->setAlias(RequestInterface::class, Request::class);
        $this->set(Request::class, fn(ContainerInterface $app) => Request::createFromGlobals());
        $this->set(HttpErrorResponse::class, function(ContainerInterface $app, ...$parameters) {
            switch($parameters[0] ?? 500) {
                case 400:
                    return $app->get(BadRequestResponse::class);
                    break;
                case 401:
                    return $app->get(UnauthorizedResponse::class);
                    break;
                case 403:
                    return $app->get(ForbiddenResponse::class);
                    break;
                case 408:
                    return $app->get(RequestTimeoutResponse::class);
                    break;
                case 500:
                    return $app->get(ServerErrorResponse::class);
                    break;
                default:
                    return $app->get(HttpErrorResponse::class, $parameters, false);
            }
        });
        $this->setAlias(ContainerInterface::class, Container::class);
        $this->setAlias(Container::class, self::class);
        $this->set(self::class, fn(ContainerInterface $app) => static::getInstance());
    }

    public function envIsProduction(): bool
    {
        return $this->config('app.env') === 'production';
    }

    private function setPath(string $path, string $base): string
    {
        if(strlen($path) > 0 && $this->isRelativePath($path)) {
            return realpath($base . '/' . $path);
        }

        throw new PathDefinitionException($path);
    }

    protected function isRelativePath(string $path): bool
    {
        return strncmp($path, '/', 1) !== 0;
    }

    protected function loadEnvFile(string $envPath, ?string $envFileName = null): void
    {
        Dotenv::createImmutable(realpath($envPath), $envFileName)->load();
    }

    protected function loadConfiguration(string $configPath): void
    {
        $this->registerSingletons([
            Configuration::class,
        ]);
        $configFiles = (new Collection(scandir($configPath)))->reject(function ($file) use ($configPath) {
            return is_dir($configPath . '/' . $file) || (!file_exists($configPath . '/' . $file));
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

    public function getPackageViewsPath(): string
    {
        return $this->packageViewsPath;
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
        if(!is_null($request)) {
            $this->set(Request::class, fn($app) => $request);
        } else {
            $request = $this->get(Request::class);
        }
        $routeInfo = $this->get(Dispatcher::class)->dispatch($request->input('_method', $request->getMethod()), $request->getUri());
        if($routeInfo[0] === Dispatcher::NOT_FOUND) {
            return $this->get(NotFoundResponse::class);
        }
        if($routeInfo[0] === Dispatcher::METHOD_NOT_ALLOWED) {
            return $this->get(MethodNotAllowedResponse::class, [$routeInfo[1]]);
        }
        try {
            $router = new Router($this, $request, $routeInfo[1], $routeInfo[2]);
            return $router->pipeRequestThroughToResponse();
        } catch(HttpException $exception) {
            return $this->get(HttpErrorResponse::class, [$exception->getCode(), $exception->getMessage()]);
        } catch(Throwable $exception) {
            if(!$this->config('app.debug', false)) {
                return $this->get(ServerErrorResponse::class);
            }
            return $this->get(ExceptionResponse::class, [$exception]);
        }
    }

    public function boot(?callable $callback = null): void
    {
        try {
            foreach($this->services as $service) {
                $this->bootService($this->get($service));
            }
            $this->bootOther($callback);
        } catch(Throwable $exception) {
            if (!$this->config('app.debug', false)) {
                $this->get(ServerErrorResponse::class)->render();
            } else {
                $this->get(ExceptionResponse::class, [$exception])->render();
            }
            if($this->config('app.env', 'production') !== 'testing') { exit; }
        }
    }

    public function bootService(ServiceInterface $serviceClass): void
    {
        $serviceClass->boot();
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
}
