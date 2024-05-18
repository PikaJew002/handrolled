# Handrolled: a minimalist framework

This project is a very minimalist framework for quickly getting an easily consumable API up and running in a few minutes.
It uses very few dependencies and the ones that have been pulled in are small and make the whole experience dope as hell.

I'll be upfront: this project exists because I got the munchies to learn how modern PHP frameworks did magical stuff like routing, dependency injection, object-relational mappers, load configuration, etc. All that magical stuff is even more cool when you pull back the curtain and dig into some code and try to implement it for yourself. Several patterns here are heavily inspired by the Laravel framework, because that's my technical background as a programmer who uses Laravel every day.

Do yourself a favor and don't use this in a production level, enterprise app. It'll probably change a good deal. However, it is all MIT licensed, so feel free to use it and/or steal bits of it you like. I always appreciate a shout out, but you can technically do whatever you want with it. _shrug_

So, get out there and light up your next idea. Or at least blow some smoke.

# Installation

The easiest way to get started quickly would be to clone the `PikaJew002/handrolled-project` repository and follow the few steps in the README (make empty directory, clone repo to directory, composer install), then head back here and read on starting with `Usage` to learn more in-depth.

Alternatively, install like so.

You can install the Handrolled framework as a Composer package like so:

```bash
composer require pikajew002/handrolled
```

Install the dependencies:

```bash
composer install
```

If you want more info on the dependencies that were used, check out the `composer.json` file. There are a few.

To tell Composer where to autoload your application code, add an autoload block to your `composer.json` file. Replace `src/` with whatever directory name you want to use (`app/`, etc).

```json
"autoload": {
    "psr-4": {
        "App\\": "src/"
    }
}
```

# Usage

To use this framework and get the most dope experience, I recommend using the Front Controller pattern.
Simply put, all requests for your application should be routed through your `index.php` in your web root directory. I favor using a directory one level above your project root (`public_html`, `public`, etc). You will need to configure your web server to use `{project_dir}/{public_dir}/index.php` as your single entry point to your application. The Laravel docs have a good [`sample Nginx configuration`](https://laravel.com/docs/deployment#nginx) that is a great starting place for configuring Nginx for the Front Controller pattern in PHP.

Your `{project_dir}/{public_dir}/index.php` file should look something like this***:

*** if you cloned the `PikaJew002/handrolled-project` repo, all the files mentioned here already exist. Nifty, eh?

```php
// {project_dir}/{public_dir}/index.php
require __DIR__.'/../vendor/autoload.php';

$app = new \PikaJew002\Handrolled\Application\Application();

$app->boot();

$app->handleRequest()->render();

```

The constructor for the `Application` class takes up to three arguments.

 - `$envPath`: The path to the directory where the `.env` file is found. Defaults to `../`, that is your `{project_dir}` or one directory above your public directory.
 - `$configPath`: The path to the directory where the configuration files are found. Defaults to `../config`.
 - `$envFileName`: The name of the `.env` file if it is different than `.env`. This is optional and will default to `.env`.

If you are doing any custom boot tasks you can abstract the booting logic into a boot file such as `/{project_dir}/boot/boot.php` and require it into the `/{project_dir}/{public_dir}/index.php` file like so:

```php
// {project_dir}/boot/boot.php
$app = new \PikaJew002\Handrolled\Application\Application();

$app->boot(function($appInstance) {
    // do some other stuff with the app instance
    // runs after all core services have been booted
});

return $app;

// {project_dir}/{public_dir}/index.php
require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../boot/boot.php';

$app->handleRequest()->render();
```

The `boot` method simply loops through the services in the `services` array in `config/app.php`.

```php
// {project_dir}/config/app.php

use PikaJew002\Handrolled\Application\Services;

return [
    // ...
    'services' => [
        Services\DatabaseService::class,
        Services\AuthService::class,
        Services\RouteService::class,
        Services\ViewService::class,
    ],
];
```

Those service classes are the minimum needed to boot the bare-bones services that an application is likely to need such as a database, routes, views, and authentication.

If you have additional set up related to those services in your own app, a good pattern to follow would be to make your own service classes and have them extend one of the base services classes (e.x. `DatabaseService`) and override the `boot` method.

```php
// {project_dir}/src/Path/To/MyDatabaseService.php

namespace App\Path\To;

use PikaJew002\Handrolled\Application\Services\DatabaseService;

class MyDatabaseService extends DatabaseService
{
    public function boot(): void
    {
        // does the basic set up from DatabaseService first
        parent::boot();

        // your application specific database set up code
    }
}
```

After implementing your own service, just replace the old service class in `config/app.php` in the `services` array.

```php
// {project_dir}/config/app.php

use PikaJew002\Handrolled\Application\Services;

return [
    // ...
    'services' => [
        \App\Path\To\MyDatabaseService::class,
        Services\AuthService::class,
        Services\RouteService::class,
        Services\ViewService::class,
    ],
];
```

If you need additional, miscellaneous set up, pass a Closure to the `boot` method on the `Application` class and it will be called after all the service classes have been booted.

It's good for simple after-everything-else-is-booted logic.
Or simply make a new service and have it extend the base `Service` class and implement the `Service` interface like so:

```php
// {progject_dir}/src/Path/To/New/ServiceClass.php

namespace App\Path\To\New;

use PikaJew002\Handrolled\Application\Service;
use PikaJew002\Handrolled\Interfaces\Service as ServiceInterface;

class MyOtherService extends Service implements ServiceInterface
{
    public function boot(): void
    {
        // your other service set up code
    }
}
```

The base service exposes the `app` property (accessed like `$this->app`) which is the underlying `Application` instance.

# Required Configuration

The setup shown here assumes you have the directory structure of the repository [`PikaJew002/handrolled-project`](https://github.com/PikaJew002/handrolled-project). That gives you these files by default

 - `.env`
 - `config/app.php`
 - `config/auth.php`
 - `config/database.php`
 - `config/route.php`
 - `public/index.php`*
 - `resources/views/`
 - `routes/`

\* see [Usage](#usage) above for an explanation of this file

## `.env`

You can copy the `.env.example` file to set environment variables required for the framework to boot out of the box.

```bash
cp vendor/pikajew002/handrolled/.env.example .env
```

This file has these environment variables defined:

```
APP_NAME=Handrolled
APP_URL=http://localhost
APP_ENV=local
APP_DEBUG=true

DB_DRIVER=mysql
DB_HOST=127.0.0.1
DB_DATABASE=handrolled
DB_USERNAME=handrolled
DB_PASSWORD=
```

## `config/`

The `config` directory contains all of your projects configuration. To keep things portable, a lot of your configuration will be loaded from your `.env` file using the `env` helper function.
The files are broken down as such:

 - `config/app.php`: Configuration needed for the application to run. There are 3 categories:
    - General: This includes name, URL, environment type, and debug mode.
    - `paths`: The project directory and where to find other important directories relative to it (routes directory, view templates directory, directory where the application cache is stored).
    - `services`: The service classes that are used to boot specific services using the `boot` method.
 - `config/auth.php`: Configuration for different authentication methods.
 - `config/database.php`: Configuration for different databases drivers.
 - `config/route.php`: Where to define different middleware stacks.

Examples of these files can be found in the [`PikaJew002/handrolled-project`](https://github.com/PikaJew002/handrolled-project/tree/main/config) repository.

## `routes/`

This directory should hold files containing groups of routes. More specifically, the file should return a `RouteGroup` instance. The routes defined on the route group will have the middleware group that the filename matches applied to it (as well as the global middleware group). If it doesn't have a matching middleware group name as defined in `config/route.php`, then just the global middleware group will be applied to the routes in the group.

An example route file should look like this:

```php
// {project_dir}/routes/web.php

use PikaJew002\Handrolled\Http\Request;
use PikaJew002\Handrolled\Http\Responses\ViewResponse;
use PikaJew002\Handrolled\Router\Definition\RouteGroup;

$route = new RouteGroup();

// your route definitions go here
$route->get('/', function(Request $request) {
    return new ViewResponse('welcome.twig.html');
});

$route->get('/other-page', [\App\Http\YourController::class, 'method']);

return $route;
```

# Next Steps

That's all you need to get your project scaffold set up.

These sections will go into more depth on their respective topic

 - [Routing](/routing.md#routing)
 - [Requests](/requests.md#requests)
 - [Responses](/responses.md#responses)
 - [Databases](/database.md#database)
 - [Authentication](/authentication.md#authentication)

 Now get out there and light up your next idea (or at least blow some smoke).
