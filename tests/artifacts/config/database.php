<?php

return [
    /*
      host: The host of the database
      dbname: The database name
      user: The username to connect to the database
      password: The password that goes with the username
      driver: The database driver to use
      port (optional): The port to connect on

      Currently supported database drivers:
        - MySQL (default)
        - PostgreSQL
        - SQLite
    */
    'driver' => env('DB_DRIVER', 'mysql'),
    'drivers' => [
        'sqlite' => [
            'user' => '',
            'password' => '',
            // path to db file on disk
            // if memory is present, don't include
            // 'path' => env('DB_PATH', ':memory:'),
            // if db is non-persistant (in memory)
            // if path is present, don't include
            'memory' => true,
            'driver' => 'pdo_sqlite',
        ],
        'mysql' => [
            'dbname' => env('DB_DATABASE', 'handrolled'),
            'user' => env('DB_USERNAME', 'handrolled'),
            'password' => env('DB_PASSWORD', ''),
            'host' => env('DB_HOST', '127.0.0.1'),
            'driver' => 'pdo_mysql',
        ],
        'pgsql' => [
            'dbname' => env('DB_DATABASE', 'handrolled'),
            'user' => env('DB_USERNAME', 'handrolled'),
            'password' => env('DB_PASSWORD', ''),
            'host' => env('DB_HOST', '127.0.0.1'),
            'driver' => 'pdo_pgsql',
        ],
    ],
];
