# Database

Databases are at the heart of most web applications today.

If you want to connect and use a database, your database configuration file (`config/database.php`) should look like this:

```php
// {project_dir}/config/database.php
return [
    /*
      dbname: The database name
      user: The username to connect to the database
      password: The password that goes with the username
      host: The host of the database
      port (optional): The port to connect on
      driver: The database driver to use

      Currently supported database drivers:
        - Whatever doctrine/dbal version 3.4 supports
    */
    'driver' => env('DB_DRIVER', 'mysql'),
    'drivers' => [
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
```

The database connection is Doctrine/DBAL under the hood to help give as broad of support as possible for different databases.

For more detailed documentation on configuration options for the different drivers, please look at [their documentation](https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/index.html).

To define middleware 'stacks', your route configuration file (`config/route.php`) should include those middleware classes like so:

```php
// {project_dir}/config/route.php

return [
    'middleware' => [
        'global' => [
            \App\Your\Middleware::class,
        ],
        'api' => [
            \PikaJew002\Handrolled\Http\Middleware\AuthenticateToken::class,
        ],
        'web' => [],
    ],
];
```

In order to use the `AuthenticateEdible` or the `AuthenticateToken` middleware (uses the drivers `cookies` and `token` respectively), you'll need to have an authentication configuration file (`config/auth.php`).

```php
// {project_dir}/config/auth.php

return [
    'user' => \App\Models\User::class,

    'drivers' => [
        'cookies' => [
            'http_only' => true,
            'secure' => false,
            'length' => 3600, // 1 hr
        ],
        'token' => [
            'class' => \App\Models\Token::class,
            'length' => 3600, // 1 hr
        ],
    ]
];
```

Right now, the supported drivers are `cookies` and `token`.
The `cookies` driver does not use PHP sessions, only raw HTTP cookies (because PHP sessions are a pain in the ass to deal with, I said what I said).
The `token` driver has to reference a Token class which implements the Token interface (`PikaJew002\Handrolled\Interfaces\Token`).
