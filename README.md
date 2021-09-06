# Handrolled: a minimalist framework

This project is a very minimalist framework for quickly getting an easily consumable API up and running in a few minutes.
It uses very few dependencies and the ones that have been pulled in are small and make the whole experience dope as hell.

## Background

I'll be upfront: this project exists because I got the munchies to learn how modern PHP frameworks did magical stuff like routing, dependency injection, object-relational mappers, load configuration, etc. All that magical stuff is even more cool when you pull back the curtain and dig into some code and try to implement it for yourself. Several patterns here are heavily inspired by the Laravel framework, because that's my technical background as a programmer that uses Laravel every day.

# Installation

The easiest way to get started quickly would be to clone the `PikaJew002/handrolled-project` repository and follow the few steps in the README (make empty directory, clone repo to directory, composer install), then head back here and read on starting with `Usage` to learn more in-depth.

Alternatively, install like so.

You can install the Handrolled framework as a Composer package like so:

```sh
composer require pikajew002/handrolled
```

Install the dependencies:

```sh
composer install
```

If you want more info on the dependencies that were used, check out the `composer.json` file. There are a few.

# Usage

To use this framework and get the most dope experience, I recommend using the Front Controller pattern.
Simply put, all requests for your application should be routed through your `index.php` in your web root directory. I favor using a directory one level above your project root (`public_html`, `public`, etc). You will need to configure your web server to use `{project_dir}/{public_dir}/index.php` as your single entry point to your application. The Laravel docs have a good sample Nginx configuration that is a great starting place for configuring Nginx for the Front Controller pattern in PHP.

Your `{project_dir}/{public_dir}/index.php` file should look something like this***:

*** if you cloned the `PikaJew002/handrolled-project` repo, all the files mentioned here already exist. Nifty, eh?

```php
// {project_dir}/{public_dir}/index.php
require __DIR__.'/../vendor/autoload.php';

$app = new \PikaJew002\Handrolled\Application\Application();

$app->bootConfig(realpath(__DIR__.'/../'), realpath(__DIR__.'/../config/'));

$app->bootRoutes(realpath(__DIR__.'/../routes/api.php'));

$app->bootDatabase();

if($app->hasBootExceptions()) {
    $app->renderExceptions();
} else {
    $response = $app->handleRequest();
    $response->render();
}
```

I find this to be a bit cluttered, so my personal preference is to extract some of the set up into a boot file such as `/{project_dir}/boot/boot.php` and require it into my `/{project_dir}/{public_dir}/index.php` file like so:

```php
// {project_dir}/boot/boot.php
$app = new \PikaJew002\Handrolled\Application\Application();
// same as $app = new \PikaJew002\Handrolled\Application\Application('../');

$app->bootConfig();
// same as $app->bootConfig('../', 'config');

$app->bootRoutes();
// same as $app->bootRoutes('routes/api.php');

$app->bootDatabase();

return $app;

// {project_dir}/{public_dir}/index.php
require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../boot/boot.php';

if($app->hasBootExceptions()) {
    $app->renderExceptions();
} else {
    $response = $app->handleRequest();
    $response->render();
}
```

# Required Configuration

There are a few more step to set up your configuration.
You'll notice in `boot/boot.php` we reference `routes/api.php`, a `../` directory, and a `config` directory. Those are the default values for the project root (arg 1 in `new Application`), the directory where the `.env` file is located (are 1 in `$app->bootConfig`), the directory where the configuration files are located (arg 2 in `$app->bootConfig`), the file where the routes are defined (arg 1 in `$app->bootRoutes`), and the database driver to use (arg 1 in `$app->bootDatabase`).
A `.env` file (and the path to it), `config` directory, and a `routes/api.php` file will be needed to boot the framework.

## .env

You can copy the `.env.example` file to set environment variables required for the framework to boot.

```
cp vendor/pikajew002/handrolled/.env.example .env
```

This file pretty much just contains a valid database configuration at this point.
At this time MySQL (`mysql`) and PostgreSQL (`pgsql`) are the only ones supported. See the PHP PDO drivers configuration for what variables are required for those.
The path to the directory that holds the `.env` file is the first parameter in the `bootConfig` function.

## config

The `config` directory must have have `database.php` which should look like this:

```php
// {project_dir}/config/database.php
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
```

This, by default, sets the required environment variables to connect to a local MySQL database.

In order to add middleware to the routes (defined in `routes/api.php`), you will be to have a `route.php` file.
This defines the global middleware applied to all routes. The order specified is the order in which they are applied.

```php
// {project_dir}/config/route.php

return [
    'middleware' => [
        //
    ],
];
```

In order to use the `AuthenticateEdible` middleware (see section below for details), you'll need to have a `auth.php` file.

```php
// {project_dir}/config/auth.php

return [
    // this defaults to \App\Models\User::class, if not specified
    'user' => \App\Models\User::class,
    'driver' => 'cookies',

    'drivers' => [
        'cookies' => [
            'http_only' => true,
            'secure' => false,
            'length' => 3600, // 1 hr
        ],
    ]
];
```

Right now, the only supported driver is `cookies`. This does not use PHP sessions, only raw cookies. Because PHP sessions are a pain in the ass to deal with. I said what I said.

## routes/api.php

The `routes/api.php` file should/can look something like this:

```php
// {project_dir}/routes/api.php

use App\Http\Controllers\UsersController;
use App\Http\Controllers\InvokableController;
use FastRoute\RouteCollector;
use PikaJew002\Handrolled\Http\Middleware\AuthenticateEdible;
use PikaJew002\Handrolled\Http\Responses\JsonResponse;
use function FastRoute\simpleDispatcher;

return simpleDispatcher(function(RouteCollector $r) {
    $r->addGroup('/api', function (RouteCollector $r) {
        $r->get('/users', [
            'class' => UsersController::class,
            'method' => 'index',
            'middleware' => AuthenticateEdible::class,
        ]);
        $r->get('/user/{id:\d+}', [
            'class' => UsersController::class,
            'method' => 'view',
            'middleware' => AuthenticateEdible::class,
        ]);
        $r->post('/user', [
            'class' => UsersController::class,
            'method' => 'store',
            'middleware' => AuthenticateEdible::class,
        ]);
        $r->delete('/user/{id:\d+}', [
            'class' => UsersController::class,
            'method' => 'destroy',
            'middleware' => AuthenticateEdible::class,
        ]);
        $r->addRoute('GET', '/closure/optional-title[/{title}]', [
            'closure' => function($title = "", Request $request) {
                return new JsonResponse(['title' => $title]);
            },
            'middleware' => AuthenticateEdible::class,
        ]);
        // Alternatively if not adding middleware:
        $r->addRoute('GET', '/closure/optional-title[/{title}]', function($title = "", Request $request) {
            return new JsonResponse(['title' => $title]);
        });
    });
    $->addRoute(['PATCH', 'PUT'], '/edit-something', [
        'class' => InvokableController::class,
        'middleware' => AuthenticateEdible::class,
    ]);
    // Alternatively if not adding middleware:
    $->addRoute(['PATCH', 'PUT'], '/edit-something', InvokableController::class);
});
```

Note: you can make and use your own middleware, just be sure it implements the middleware interface (`PikaJew002\Handrolled\Interfaces\Middleware`).

This routes file assumes a few things:

First, that you have created `UsersController` and `InvokableController` classes somewhere and are namespaced under `App\Http\Controllers`.
Second, in order to use the `AuthenticateEdible` middleware you will need to have a `User` model (which the class is defined in `config/auth.php`) that looks something like this (must implement the interface and use the trait):

```php
// {project_dir}/src/Models/User.php

namespace App\Models;

use PikaJew002\Handrolled\Database\Orm\Entity;
use PikaJew002\Handrolled\Interfaces\User as UserInterface;
use PikaJew002\Handrolled\Traits\UsesAuthCookie;

class User extends Entity implements UserInterface
{
    use UsesAuthCookie;

    protected string $tableName = 'users';

    // Entity database columns
    public $id;
    public $email;
    public $first_name;
    public $last_name;
    public $password_hash;
    public $created_at;
    public $updated_at;

    // must be implemented for UserInterface
    public function getId()
    {
        return $this->id;
    }

    // must be implemented for UserInterface
    public function getUsername()
    {
        return $this->email;
    }

    // must be implemented for UserInterface
    public function getPasswordHash()
    {
        return $this->password_hash;
    }

    // used in LoginController, should you choose to implement it
    public static function checkCredentials(string $username, string $password): ?self
    {
        $user = self::find([
            'conditions' => ['email' => $username],
        ]);
        if(!empty($user) && password_verify($password, $user[0]->getPasswordHash())) {
            return $user[0];
        }

        return null;
    }

    /*
     * -> must implement in every class that extends Entity
     */
    public static function getTableName(): string
    {
        return $tableName ?? "users";
    }
}
```

This example model (or database entity) defines the table name and columns on the class and assumes the database already has this table created.

Along with a `UsersController.php` file:

```php
// {project_dir}/{project_src}/Http/Controllers/UsersController.php
namespace App\Http\Controllers;

use App\Models\User;
use PikaJew002\Handrolled\Exceptions\Http\HttpException;
use PikaJew002\Handrolled\Http\Responses\JsonResponse;
use PikaJew002\Handrolled\Http\Responses\NotFoundResponse;
use PikaJew002\Handrolled\Interfaces\Response;

class UsersController
{
    public function index(): Response
    {
        $users = User::all();
        return new JsonResponse(['users' => $users]);
    }

    public function view($id): Response
    {
        $user = User::findById($id);
        if(is_null($user)) {
            return new NotFoundResponse();
        }
        return new JsonResponse(['user' => $user]);
    }

    public function store(Request $request): Response
    {
        $user = new User;
        $user->email = $request->input('email');
        $user->first_name = $request->input('first_name');
        $user->last_name = $request->input('last_name');
        $user->save();

        return new JsonResponse(['user' => $user], 201);
    }

    public function destroy($id): Response
    {
        $user = User::findById($id);
        if(is_null($user)) {
            return new NotFoundResponse();
        }
        if($user->delete()) {
            return new JsonResponse(['user' => $user]);
        }

        throw new HttpException(500, 'Database error! Could not delete user!');
    }
}
```

And a `InvokableController.php` file:

```php
// {project_dir}/{project_src}/Http/Controllers/InvokableController.php
namespace App\Http\Controllers;

use PikaJew002\Handrolled\Http\Responses\JsonResponse;
use PikaJew002\Handrolled\Interfaces\Response;

class InvokableController
{
    public function __invoke(): Response
    {
        return new JsonResponse(['data' => ['message' => 'success']]);
    }
}
```

These example controllers illustrate how to use controllers, models, and return responses.

To use your own classes/code, be sure to add an autoload block to your `composer.json` file like so (replace `src/` with wherever your project classes are located if different):

```json
"autoload": {
    "psr-4": {
        "App\\": "src/"
    }
}
```

# Wrapping Up

That's about it. Now get out there and light up your next idea (or at least blow some smoke).

# License
The Handrolled framework is open-sourced software licensed under the MIT license.
