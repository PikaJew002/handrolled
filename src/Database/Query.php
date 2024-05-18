<?php

namespace PikaJew002\Handrolled\Database;

use Exception;
use PikaJew002\Handrolled\Database\Traits\UsesConnection;

class Query
{
    use UsesConnection;

    public static function raw(string $sql, array $parameters = [], array $types = []): int
    {
        $result = static::getDatabaseConnection()->executeStatement($sql, $parameters, $types);

        return (int) $result;
    }
}
