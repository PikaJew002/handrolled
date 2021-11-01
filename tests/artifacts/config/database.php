<?php

use PikaJew002\Handrolled\Database\Implementations\MySQL;
use PikaJew002\Handrolled\Database\Implementations\PostgreSQL;;

return [
    /*
      host: The host of the database
      database: The Database name
      username: The username to connect to the database
      password: The password that goes with the username
      class: The implementation class that extends the PDO class
      port (optional): The port to connect on

      Currently supported database drivers:
        - MySQL (default)
        - PostgreSQL
    */
    'driver' => env('DB_DRIVER', 'mysql'),
    'drivers' => [
        'mysql' => [
            'host' => env('DB_HOST', '127.0.0.1'),
            'database' => env('DB_DATABASE', 'handrolled'),
            'username' => env('DB_USERNAME', 'handrolled'),
            'password' => env('DB_PASSWORD', ''),
            'class' => MySQL::class,
        ],
        'pgsql' => [
            'host' => env('DB_HOST', '127.0.0.1'),
            'database' => env('DB_DATABASE', 'handrolled'),
            'username' => env('DB_USERNAME', 'handrolled'),
            'password' => env('DB_PASSWORD', ''),
            'class' => PostgreSQL::class,
        ],
    ],
];
