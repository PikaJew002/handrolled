# Handrolled: a minimalist framework

This project is a very minimalist framework for quickly getting an easily consumable API up and running in a few minutes.
It uses very few dependencies and the ones that have been pulled in are small and make the whole experience dope as hell.

I'll be upfront: this project exists because I got the munchies to learn how modern PHP frameworks did magical stuff like routing, dependency injection, object-relational mappers, load configuration, etc. All that magical stuff is even more cool when you pull back the curtain and dig into some code and try to implement it for yourself. Several patterns here are heavily inspired by the Laravel framework, because that's my technical background as a programmer that uses Laravel every day.

Do yourself a favor and don't use this in a production level, enterprise app. It'll probably change a good deal. However, it is all MIT licensed, so feel free to use it and/or steal bits of it you like. I always appreciate a shout out, but you can technically do whatever you want with it. _shrug_

So get out there and light up your next idea. Or at least blow some smoke.

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

# Usage

To use this framework and get the most dope experience, I recommend using the Front Controller pattern.
Simply put, all requests for your application should be routed through your `index.php` in your web root directory. I favor using a directory one level above your project root (`public_html`, `public`, etc). You will need to configure your web server to use `{project_dir}/{public_dir}/index.php` as your single entry point to your application. The Laravel docs have a good sample Nginx configuration that is a great starting place for configuring Nginx for the Front Controller pattern in PHP.

Your `{project_dir}/{public_dir}/index.php` file should look something like this***:

*** if you cloned the `PikaJew002/handrolled-project` repo, all the files mentioned here already exist. Nifty, eh?

```php
// {project_dir}/{public_dir}/index.php
require __DIR__.'/../vendor/autoload.php';

$app = new \PikaJew002\Handrolled\Application\Application();

$app->boot();

$app->handleRequest()->render();

```

If you are doing any custom boot tasks you can abstract the booting logic into a boot file such as `/{project_dir}/boot/boot.php` and require it into my `/{project_dir}/{public_dir}/index.php` file like so:

```php
// {project_dir}/boot/boot.php
$app = new \PikaJew002\Handrolled\Application\Application();
// same as: $app = new \PikaJew002\Handrolled\Application\Application('../', '../config');

$app->boot(function($appInstance) {
    // do some other stuff with the app instance
    // runs after all services have been booted
});

return $app;

// {project_dir}/{public_dir}/index.php
require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../boot/boot.php';

$app->handleRequest()->render();
```

The `boot` method simply loops through the services in the `services` array in `config/app.php`. Those service classes are the bare minimum needed to boot the bare-bones services that an application is likely to need such as databases, routes, views, authentication, etc.

Feel free to source dive those service classes to see what all they do. If you have additional set up related to those services in your own app, a good pattern to follow would be to make your own service classes and have them extend one of the base services classes (e.x. `ApplicationService`) and implement the `Service` interface like so:

```php
// {progject_dir}/src/Path/To/MyApplicationService.php

namespace App\Path\To;

use PikaJew002\Handrolled\Application\Services\ApplicationService;
use PikaJew002\Handrolled\Interfaces\Service as ServiceInterface;

class MyApplicationService extends ApplicationService implements ServiceInterface
{
    public function boot(): void
    {
        // does the basic set up first
        parent::boot();

        // your application set up code
    }
}
```

After implementing your own service, just replace the old service class in `config/app.php` in the `services` array.

If you need additional, miscellaneous set up, pass a Closure to the `boot` method on the `Application` class (takes the `Application` instance, or rather itself, as the only argument) and it will be called after all the service classes have been booted.
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

The base service exposes the `app` property (accessed like `$this->app`) which is the underlying `Application` instance passed by reference.

Be sure to add your other service class to the `services` array in `config/app.php` for it to be run when the application is booted.

# Required Configuration

The setup shown here assumes you have the directory structure of the repository [`PikaJew002/handrolled-project`](https://github.com/PikaJew002/handrolled-project). That is you need the following files and directories

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

You can copy the `.env.example` file to set environment variables required for the framework to boot.

```bash
cp vendor/pikajew002/handrolled/.env.example .env
```

The first argument in the `Application` constructor take the path to the directory where this file is contained (default value is `../`, one directory above the public directory where a new `Application` instance is new'd up). It is always assumed to be named `.env`. That's the convention the whole PHP universe uses and I think that's a safe bet.

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

The `config` directory must have a number of files, such as `app.php` which defines the paths to the project root, routes directory (see [`routes/`](#routes) below), front-end resources (views and such), and the cache (where views and other resources are cached in production). Also, `database.php`, `route.php`, and `auth.php`. Examples of these files can be found in the [`PikaJew002/handrolled-project`](https://github.com/PikaJew002/handrolled-project/tree/main/config) repository.

The second argument in the `Application` constructor take the path to the directory where theses file are contained (default value is `../config`, a sibling directory to the public directory where a new `Application` instance is new'd up).

Most configuration variables try to use a value defined in `.env`, but then define a backup value if the variable is not found by use of the `env` helper method.

## `routes/`

This directory should hold files containing groups of routes. More specifically, the file should return a `RouteGroup` instance. This instance will have the middleware group that the filename matches applied to it (as well as the global middleware group).

An example route file should look like this:

```php
// {project_dir}/routes/web.php

use PikaJew002\Handrolled\Router\Definition\RouteGroup;

$routes = new RouteGroup();

// your route definitions go here

return $routes;
```

These routes will automatically have the middleware group `web` applied to in (as well as the `global` middleware group). These middleware stacks can be found in the `config/route.php` file discussed in more detail in [Routing](/routing.md#routing).

# Wrapping Up

That's about it. Now get out there and light up your next idea (or at least blow some smoke).
