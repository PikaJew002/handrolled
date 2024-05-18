<?php

namespace PikaJew002\Handrolled\Database\Traits;

use Doctrine\DBAL\Connection;
use PikaJew002\Handrolled\Application\Application;

trait UsesConnection
{
    protected static string $connection = Connection::class;

    public static function getDatabaseConnection(): Connection
    {
        return Application::getInstance()->get(static::$connection);
    }
}
