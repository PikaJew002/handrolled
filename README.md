# Handrolled: a minimalist framework

This project is a very minimalist framework for quickly getting an easily consumable API up and running in a few minutes.
It uses very few dependencies and the ones that have been pulled in are small and make the whole experience simple.

## Background

I'll be upfront: this project exists because I had a hankering to learn how modern PHP frameworks did magical stuff like routing, dependency injection containers, object-relational mappers, load configuration, etc. All that magical stuff is even more cool when you pull back the curtain and dig into some code and try to implement it for yourself. Several patterns here are heavily inspired by the Laravel framework, because that's my technical background as a programmer that uses Laravel every day.

# Installation

I'll be putting this up on packagist.org in the near future. When that happens, you'll be able to install it with composer.

```
composer require pikajew002/handrolled
```

# Usage

To use this framework and get the most dope experience, I recommend using the Front Controller pattern. Simply put, all requests for your application should be routed through your `index.php` in your web root directory. I favor using a directory one level above your project root (`public_html`, `public`, etc). You will need to configure your web server to use `/{project_dir}/{public_dir}/index.php` as your single entry point to your application. The Laravel docs have a good sample Nginx configuration that is a great starting place for configuring Nginx for the Front Contoller pattern in PHP.

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

There are a few more step to set up your configuration. You'll notice in the `boot.php` we reference a `/routes/api.php` file and a `/config/` directory.
This file, directory, and a `.env` file will be needed to boot the framework.

The `/routes/api.php` file should look something like this:

```php
// /routes/api.php
use PikaJew002\Handrolled\Http\Controllers\UsersController;
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

# License
The Handrolled framework is open-sourced software licensed under the MIT license.
