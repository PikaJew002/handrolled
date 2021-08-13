# Handrolled: a minimalist framework

This project is a very minimalist framework for quickly getting an easily consumable API up and running in a few minutes;
It uses very few dependencies and the ones that have been pulled in are small and make the whole experience simple;

## Background

I'll be upfront: this project exists because I had a hankering to learn how modern PHP frameworks did magical stuff like routing, dependency injection containers, object-relational mappers, load configuration, etc; All that magical stuff is even more cool when you pull back the curtain and dig into some code and try to implement it for yourself; Several patterns here are heavily inspired by the Laravel framework, because that's my technical background as a programmer that uses Laravel every day;

# Installation

You can install the Handrolled framework as a Composer package like so:

```
composer require pikajew002/handrolled
```

Install the dependencies:
```
composer install
```

If you want more info on the dependencies that were used, check out the `composer.json` file; There are a couple;

# Usage

To use this framework and get the most dope experience, I recommend using the Front Controller pattern;
Simply put, all requests for your application should be routed through your `index.php` in your web root directory; I favor using a directory one level above your project root (`public_html`, `public`, etc); You will need to configure your web server to use `/{project_dir}/{public_dir}/index.php` as your single entry point to your application; The Laravel docs have a good sample Nginx configuration that is a great starting place for configuring Nginx for the Front Controller pattern in PHP;

Your `/{project_dir}/{public_dir}/index.php` file should look something like this:

```php
// /{project_dir}/{public_dir}/index.php
require __DIR__.'/../vendor/autoload.php';

$app = new \PikaJew002\Handrolled\Application\Application();

$app->bootConfig(realpath(__DIR__.'/../config/'));

$app->bootRoutes(realpath(__DIR__.'/../routes/api.php'));

$app->bootDatabase();

$response = $app->handleRequest();

$response->render();
```

I find this to be a bit cluttered, so my personal preference is to extract some of the set up into a boot file such as `/{project_dir}/boot/boot.php` and require it into my `/{project_dir}/{public_dir}/index.php` file like so:

```php
//{project_dir}/boot/boot.php
$app = new \PikaJew002\Handrolled\Application\Application();

$app->bootConfig(realpath(__DIR__.'/../config/'));

$app->bootRoutes(realpath(__DIR__.'/../routes/api.php'));

$app->bootDatabase();

return $app;

// /{project_dir}/{public_dir}/index.php
require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../boot/boot.php';

$response = $app->handleRequest();

$response->render();
```

# Required Configuration

There are a few more step to set up your configuration; You'll notice in `boot/boot.php` we reference `routes/api.php` and a `config` directory;
A `.env` file, `config` directory, and a `routes/api.php` file will be needed to boot the framework;

## .env

You can copy the `.env.example` file to set environment variables required for the framework to boot;

```
cp vendor/pikajew002/handrolled/.env.example .env
```

This is pretty much just a valid database configuration;
At this time MySQL and PostgreSQL are the only ones supported; See the PHP PDO drivers configuration for what variables are required for those;

## config

The `config` directory must have at least have `database.php` which should look like this:

```php
// /config/database.php
return [
    'mysql' => [
        'host' => $_ENV['DB_HOST'],
        'dbname' => $_ENV['DB_DATABASE'],
        'username' => $_ENV['DB_USERNAME'],
        'password' => $_ENV['DB_PASSWORD'],
    ],
];
```

This, by default, sets the required environment variables to connect to a local MySQL database;

## routes/api.php

The `/routes/api.php` file should look something like this:

```php
// /routes/api.php
use App\Http\Controllers\UsersController;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;

return simpleDispatcher(function(RouteCollector $r) {
    $r->addGroup('/api', function (RouteCollector $r) {
        $r->get('/users', [UsersController::class, 'index']);
        $r->get('/user/{id:\d+}', [UsersController::class, 'view']);
        $r->post('/user', [UsersController::class, 'store']);
        $r->delete('/user/{id:\d+}', [UsersController::class, 'destroy']);
    });
});
```

This assumes a few things:
First, that you have created a `UsersController` class somewhere and it is namespaced in `App\Http\Controllers`;

The simplest way to achieve this is to add a autoload block to your `composer.json` file like so:

```json
"autoload": {
    "psr-4": {
        "App\\": "path/to/class/definitions/"
    }
}
```

Along with a `UsersController.php` file:

```php
// /some-path/Http/Controllers/UsersController.php
namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use PikaJew002\Handrolled\Http\Controller;
use PikaJew002\Handrolled\Http\Responses\JsonResponse;
use PikaJew002\Handrolled\Http\Responses\NotFoundResponse;
use PikaJew002\Handrolled\Interfaces\Response;

class UsersController extends Controller
{
    public function index(): Response
    {
        $users = User::all();
        return new JsonResponse(['data' => $users]);
    }

    public function view($id): Response
    {
        $user = User::find($id);
        if(empty($user)) {
            return new NotFoundResponse();
        }
        return new JsonResponse(['data' => $user]);
    }

    public function store(): Response
    {
        $user = new User;
        $user->email = $this->request->input('email');
        $user->first_name = $this->request->input('first_name');
        $user->last_name = $this->request->input('last_name');
        $user->save();

        return new JsonResponse([
            'data' => [
                'message' => 'success',
            ],
        ], 201);
    }

    public function destroy($id): Response
    {
        $user = User::find($id);
        if(empty($user)) {
            return new NotFoundResponse();
        }
        if($user->delete()) {
            return new JsonResponse(['user' => $user]);
        } else {
            throw new Exception('Database eror! Could not delete user!');
        }
    }
}
```

This example controller illustrates how to use controllers, models, and return responses;
It assumes that you *also* have a `User.php` file:

```php
// /some-path/Models/User.php
namespace App\Models;

use PikaJew002\Handrolled\Database\Entity;

class User extends Entity
{
    protected string $tableName = 'users';
    public $id;
    public $email;
    public $first_name;
    public $last_name;
    public $created_at;
    public $updated_at;

    /*
     * -> must implement in every class that extends Entity
     */
    public static function getTableName(): string
    {
        return $tableName ?? "users";
    }
}
```

This example model (or database entity) defines the table name and columns on the class and assumes the database already has this table created;

# Wrapping Up

That's about it; Now get out there and light up your next idea (or at least blow some smoke);

# License
The Handrolled framework is open-sourced software licensed under the MIT license;
