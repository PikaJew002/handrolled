<?php

namespace PikaJew002\Handrolled\Application\Services;

use PikaJew002\Handrolled\Application\Service;
use PikaJew002\Handrolled\Database\Orm\Exceptions\DatabaseDriverException;
use PikaJew002\Handrolled\Interfaces\Container;
use PikaJew002\Handrolled\Interfaces\Database;
use PikaJew002\Handrolled\Interfaces\Service as ServiceInterface;
use PikaJew002\Handrolled\Support\Configuration;

class DatabaseService extends Service implements ServiceInterface
{
    public function boot(): void
    {
        $config = $this->app->get(Configuration::class);
        if(in_array($config->get('database.driver'), array_keys($config->get('database.drivers')))) {
            $driver = $config->get('database.driver');
            $dbConfig = $config->get("database.drivers.{$driver}");
            $this->app->set($dbConfig['class'], function(Container $c) use ($driver) {
                  $class = $c->config("database.drivers.{$driver}.class");
                  return new $class(
                    $c->config("database.drivers.{$driver}.host"),
                    $c->config("database.drivers.{$driver}.database"),
                    $c->config("database.drivers.{$driver}.username"),
                    $c->config("database.drivers.{$driver}.password")
                );
            });
            $this->app->setAlias(Database::class, $dbConfig['class']);
        } else {
            throw new DatabaseDriverException($config->get('database.driver'));
        }
    }
}
