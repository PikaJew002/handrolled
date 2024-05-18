<?php

namespace PikaJew002\Handrolled\Database\Schema;

use Closure;
use Doctrine\DBAL\Schema\Schema as BaseSchema;
use Doctrine\DBAL\Schema\Table as BaseTable;
use Exception;
use PikaJew002\Handrolled\Database\Traits\UsesConnection;

class Schema extends BaseSchema
{
    use UsesConnection;

    public static function create(string $tableName, Closure $callable)
    {
        $schema = new static;
        $db = static::getDatabaseConnection();
        $platform = $db->getDatabasePlatform();
        $table = new Table($schema->createTable($tableName), $platform);
        $callable($table);
        $queries = $schema->toSql($platform);
        foreach($queries as $query) {
            $db->executeStatement($query);
        }
    }
}
