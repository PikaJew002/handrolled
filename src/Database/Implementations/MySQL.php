<?php

namespace PikaJew002\Handrolled\Database\Implementations;

use PDO;
use PikaJew002\Handrolled\Interfaces\Database;

class MySQL extends PDO implements Database
{
    public function __construct(string $host, string $database, string $username, string $password, string $port = null, array $options = null)
    {
        parent::__construct(
            $this->makeDSN($host, $database, "", "", $port),
            $username,
            $password,
            $options ?? [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    }

    public function makeDSN(string $host, string $database, string $username, string $password, ?string $port): string
    {
        return is_null($port) ? "mysql:host=$host;dbname=$database" : "mysql:host=$host;port=$port;dbname=$database";
    }
}
