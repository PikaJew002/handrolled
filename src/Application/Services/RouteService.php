<?php

namespace PikaJew002\Handrolled\Application\Services;

use FastRoute\Dispatcher;
use PikaJew002\Handrolled\Support\Collection;
use PikaJew002\Handrolled\Application\Service;
use PikaJew002\Handrolled\Interfaces\Container as ContainerInterface;
use PikaJew002\Handrolled\Interfaces\Service as ServiceInterface;
use PikaJew002\Handrolled\Router\Definition\RouteGroup;
use PikaJew002\Handrolled\Router\Router;

class RouteService extends Service implements ServiceInterface
{
    public function boot(): void
    {
        $routesPath = $this->app->getRoutesPath();
        $routeFiles = (new Collection(scandir($routesPath)))->reject(function($file) use ($routesPath) {
            return $file === '.' || $file === '..' || (! file_exists($routesPath .'/'. $file));
        })->mapWithKeys(function($fileName) use ($routesPath) {
            return [explode('.', $fileName)[0] => require($routesPath .'/'. $fileName)];
        })->all();
        $middlewareConfig = $this->app->config('route.middleware');
        $allRoutes = new RouteGroup();
        foreach($routeFiles as $name => $routes) {
            if(!is_null($middlewareConfig[$name])) {
                $allRoutes->addExistingGroup($routes)->middleware($middlewareConfig[$name]);
            }
        }
        $allRoutes->addMiddleware(is_null($middlewareConfig['global']) ? [] : $middlewareConfig['global']);
        $this->app->set(Dispatcher::class, function(ContainerInterface $app) use ($allRoutes) {
            return Router::processRoutes($allRoutes);
        });
    }
}
