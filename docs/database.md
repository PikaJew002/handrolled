# Database

Databases are at the heart of most web applications today. 

If you want to connect and use a database your `config/database.php` file should look like this:

```php
// {project_dir}/config/database.php
use PikaJew002\Handrolled\Database\Implementations\MySQL;

return [
    /*
      host: The host of the database
      database: The Database name
      username: The username to connect to the database
      password: The password that goes with the username
      class: The implementation class that extends the PDO class
      port (optional): The port to connect on

      Currently supported database drivers:
        - MySQL
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
    ],
];
```

You can take a look at the database implementation classes in the `PikaJew002/handrolled-package` repository to see their specifics, but the short and skinny is a class that extends the native `PDO` class and implements the `Database` interface. But this is only half of the implementation story. For full 'support', we want the database driver to seamlessly integrate with our database models and with our ORM.

To define middleware 'stacks', your `config/route.php` files should include those middleware classes like so:

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

In order to use the `AuthenticateEdible` or the `AuthenticateToken` middleware (uses the drivers `cookies` and `token` respectively), you'll need to have a `auth.php` file.

```php
// {project_dir}/config/auth.php

return [
    // this defaults to \App\Models\User::class, if not specified
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
The `cookies` driver does not use PHP sessions, only raw cookies (because PHP sessions are a pain in the ass to deal with, I said what I said).
The `token` driver has to reference a Token class which implements the Token interface (`PikaJew002\Handrolled\Interfaces\Token`).
