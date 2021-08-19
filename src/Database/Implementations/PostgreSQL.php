<?php

namespace PikaJew002\Handrolled\Database\Implementations;

use PDO;
use PikaJew002\Handrolled\Interfaces\Database;

class PostgreSQL extends PDO implements Database
{
    public function __construct(string $host, string $database, string $username, string $password, string $port = null, array $options = null)
    {
        parent::__construct(
            $this->makeDSN($host, $database, $username, $password, $port),
            null,
            null,
            $options
        );
    }

    public function makeDSN(string $host, string $database, string $username, string $password, ?string $port): string
    {
        return is_null($port) ? "pgsql:host=$host;dbname=$database;user=$username;password=$password" : "pgsql:host=$host;port=$port;dbname=$database;user=$username;password=$password";
    }
}
