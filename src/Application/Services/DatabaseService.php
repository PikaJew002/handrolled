<?php

namespace PikaJew002\Handrolled\Application\Services;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use PikaJew002\Handrolled\Application\Service;
use PikaJew002\Handrolled\Database\Exceptions\DatabaseDriverException;
use PikaJew002\Handrolled\Interfaces\Container;
use PikaJew002\Handrolled\Interfaces\Service as ServiceInterface;
use PikaJew002\Handrolled\Support\Configuration;

class DatabaseService extends Service implements ServiceInterface
{
    protected Configuration $config;

    public function boot(): void
    {
        $this->config = $this->app->get(Configuration::class);
        if(!$this->hasDatabaseDriver()) {
            throw new DatabaseDriverException($this->driver());
        }
        $databaseDriverConfig = $this->driverConfig();
        $this->app->set(Connection::class, function(Container $c) use ($databaseDriverConfig) {
            return DriverManager::getConnection($databaseDriverConfig);
        });
        $this->app->registerSingletons([
            Connection::class,
        ]);
    }

    private function hasDatabaseDriver(): bool
    {
        return in_array($this->config->get('database.driver'), array_keys($this->config->get('database.drivers')));
    }

    private function driver(): string
    {
        return $this->config->get('database.driver');
    }

    private function driverConfig(): array
    {
        return $this->config->get("database.drivers.{$this->driver()}");
    }
}
