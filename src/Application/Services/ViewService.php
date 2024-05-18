<?php

namespace PikaJew002\Handrolled\Application\Services;

use PikaJew002\Handrolled\Application\Service;
use PikaJew002\Handrolled\Interfaces\Service as ServiceInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Loader\LoaderInterface;

class ViewService extends Service implements ServiceInterface
{
    public function boot(): void
    {
        $this->app->registerSingletons([
            Environment::class,
            FilesystemLoader::class,
        ]);
        $this->app->setAlias(LoaderInterface::class, FilesystemLoader::class);
        $this->app->get(LoaderInterface::class)->setPaths([$this->app->getViewsPath(), $this->app->getPackageViewsPath()]);
        if($this->app->envIsProduction()) {
            $this->app->get(Environment::class)->setCache($this->app->getCachePath());
        } else {
            $this->app->get(Environment::class)->enableDebug();
        }
    }
}
