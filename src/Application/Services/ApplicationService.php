<?php

namespace PikaJew002\Handrolled\Application\Services;

use PikaJew002\Handrolled\Application\Application;
use PikaJew002\Handrolled\Application\Service;
use PikaJew002\Handrolled\Container\Container;
use PikaJew002\Handrolled\Http\Request;
use PikaJew002\Handrolled\Interfaces\Container as ContainerInterface;
use PikaJew002\Handrolled\Interfaces\Service as ServiceInterface;
use PikaJew002\Handrolled\Support\Configuration;

class ApplicationService extends Service implements ServiceInterface
{
    public function boot(): void
    {
        $this->app->registerSingletons([
            Application::class,
            Request::class,
        ]);
        $this->app->set(Request::class, function(ContainerInterface $app) {
            return Request::createFromGlobals();
        });
        $this->app->set(Application::class, fn(ContainerInterface $app) => Application::getInstance());
        $this->app->setAlias(Container::class, Application::class);
        $this->app->setAlias(ContainerInterface::class, Application::class);
    }
}
