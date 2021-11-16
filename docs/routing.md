# Routing

The best place to start in explaining how routing works in a Handrolled application is the directory where your route group definitions are stored. This directory is wherever as you define in your `.env` file and/or your `config/app.php` file under `paths.routes`:

```php
// {project_dir}/config/app.php
// ..
'paths' => [
    // ..
    'routes' => env('APP_ROUTES', 'routes'), // defined in {project_dir}/.env
    // ..
],
// ..
```

Once you have your `routes/` directory defined, all of the files found in that directory will be assumed to `return` a `RouteGroup` like:

```php
// {project_dir}/routes/api.php
use PikaJew002\Handrolled\Router\Definition\RouteGroup;
// all routes defined in this file will be prefixed with '/api'
$route = new RouteGroup('/api');
// ..
return $route;
```

On this `RouteGroup` instance, you can define routes or sub route groups.

## Route Definitions

A route must have a method (or methods), URI, and handler.
The method/methods can be a string (for one) or an array (for multiple), but must be a valid HTTP method (`GET`, `HEAD`, `POST`, `PUT`, `PATCH`, `DELETE`).

```php
// {project_dir}/routes/file.php
use PikaJew002\Handrolled\Http\Response;
use PikaJew002\Handrolled\Router\Definition\RouteGroup;

$route = new RouteGroup();

// method, URI, handler
$route->route('GET', '/', function() {
    // The only requirement it it must return a Response instance
    return new Response(['message' => 'success']);
});

return $route;
```

You can use the `route` method and provide the methods as the first parameter or use any of the alias methods:

```php
use PikaJew002\Handrolled\Http\Response;

// same as $route->route('GET', '/uri', fn() => new Response(['message' => 'success']));
$route->get('/uri', fn() => new Response(['message' => 'success']));

// same as $route->route('POST', '/uri', fn() => new Response(['message' => 'success']));
$route->post('/uri', fn() => new Response(['message' => 'success']));

// same as $route->route('PUT', '/uri', fn() => new Response(['message' => 'success']));
$route->put('/uri', fn() => new Response(['message' => 'success']));

// same as $route->route('PATCH', '/uri', fn() => new Response(['message' => 'success']));
$route->patch('/uri', fn() => new Response(['message' => 'success']));

// same as $route->route('DELETE', '/uri', fn() => new Response(['message' => 'success']));
$route->delete('/uri', fn() => new Response(['message' => 'success']));
```

The handler parameter can be a `Closure` (like is shown above) or an array with a class string and method name:

```php
// {project_dir}/routes/web.php
use App\Http\Controllers\UsersController;

$route->route('GET', '/users', [UsersController::class, 'index']);

// {project_dir}/src/Http/Controllers/UsersController.php
namespace App\Http\Controllers;

use PikaJew002\Handrolled\Http\Response;

class UsersController
{
    public function index(): Response
    {
        // ..
        return new Response(['message' => 'success']);
    }
}
```

Or an [invokable class](https://www.php.net/manual/en/language.oop5.magic.php#object.invoke):

```php
// {project_dir}/routes/web.php
use App\Http\Controllers\InvokableController;

$route->route('GET', '/users', InvokableController::class);

// {project_dir}/src/Http/Controllers/InvokableController.php
namespace App\Http\Controllers;

use PikaJew002\Handrolled\Http\Response;

class InvokableController
{
    public function __invoke(): Response
    {
        // ..
        return new Response(['message' => 'success']);
    }
}
```

A `Response` instance must be returned from the `Closure`, class method, or `__invoke` method on the invokable class. If you define route parameters in the URI of the route definition they must be defined as a parameter on the handler. Any type hinted (to s resolvable class instance) parameters in the handler will be resolved from the container after route parameters (if specified).

```php
use PikaJew002\Handrolled\Http\Request;
use PikaJew002\Handrolled\Http\Response;

$route->get('/user/{id}', function($id, Request $request) {
    // ..
    return new Response(['id' => $id]);
});
```

By default `{id}` will match the regex `[^/]+` so that `/user/foo` will match `/user/{id}`, but `/user/foo/bar` will not.

You can specify your own regex, for example to only match numeric ids:

```php
use PikaJew002\Handrolled\Http\Request;
use PikaJew002\Handrolled\Http\Response;

$route->get('/user/{id:\d+}', function($id, Request $request) {
    // ..
    return new Response(['id' => $id]);
});
```

However there are limitations to this. For example, your regex cannot use capturing groups.

To specify optional parts of a route, enclose the optional part(s) in `[...]`. Optional parts can be nested as well, but be sure to put all optional parts at the tail of the URI.

```php
use PikaJew002\Handrolled\Http\Request;
use PikaJew002\Handrolled\Http\Response;

// will match /user/52 and /user/52/bob
$route->get('/user/{id:\d+}[/{name}]', function($id, $name = null, Request $request) {
    // ..
    return new Response(['id' => $id, 'name' => $name]);
});

// will match /user, /user/52, /user/52/bob, and /user/52/bob/view
$route->get('/user[/{id:\d+}[/{name}[/view]]]', function($id = null, $name = null, Request $request) {
    // ..
    return new Response(['id' => $id, 'name' => $name]);
});
```

For full documentation on how route URIs are parsed, check out the [Fast Route documentation](https://github.com/nikic/FastRoute#defining-routes).

If you have route parameters in optional parts of the URI, be sure to give them default values in the `Closure`/method signature. If the optional part of the URI is not included the default value will be used instead.

## Route Groups

You can also define sub route groups on the `RouteGroup` instance to group routes in a nested fashion.
To add a common prefix to all routes in a group, pass a string as the first parameter and a callback that accepts a `RouteGroup` instance as the second parameter.

```php
use PikaJew002\Handrolled\Router\Definition\RouteGroup;

$route->group('/prefix', function(RouteGroup $route) {
    // add some more routes, groups, etc
});
```

If your group doesn't need a prefix, pass the callback as the first and only parameter.

```php
use PikaJew002\Handrolled\Router\Definition\RouteGroup;

$route->group(function(RouteGroup $route) {
    // add some more routes, groups, etc
});
```

This man be useful if you want to group routes by the [middleware](#middleware) applied to them and don't want to prefix them.

## Middleware

Middleware can be applied on a per route basis:

```php
use App\Http\Controllers\UsersController;
use PikaJew002\Handrolled\Http\Middleware\AuthenticateToken;

$route->post('/api/user', [UsersController::class, 'store'])->middleware(AuthenticateToken::class);
```

Or on a route group (applied to all routes/route groups in the group):

```php
use App\Http\Controllers\UsersController;
use App\Http\Middleware\OtherMiddleware;
use PikaJew002\Handrolled\Http\Middleware\AuthenticateToken;
use PikaJew002\Handrolled\Router\Definition\RouteGroup;

$route->group('/api', function(RouteGroup $route) {
    $route->get('/users', [UsersController::class, 'index']);
    $route->group('/user', function(RouteGroup $route) {
        $route->post('', [UsersController::class, 'store']);
        $route->put('/{id:\d+}', [UsersController::class, 'update']);
    });
})->middleware([
    AuthenticateToken::class,
    OtherMiddleware::class,
]);
```

Additionally, there is a built in mechanism for applying reusable groups of middleware by defining middleware groups in your `config/route.php` file.

```php
// {project_dir}/config/route.php

return [
    'middleware' => [
        'global' => [
            \App\Http\Middleware\SomeGlobalMiddleware::class,
        ],
        'api' => [
            // ..
        ],
        'web' => [
            // ..
        ],
        'auth:web' => [
            \PikaJew002\Handrolled\Http\Middleware\AuthenticateEdible::class,
        ],
        'auth:api' => [
            \PikaJew002\Handrolled\Http\Middleware\AuthenticateToken::class,
        ],
        'othergroup' => [
            // ..
        ],
    ],
];
```

The group `global` will be applied to all route definitions. If you have a file (ex. `routes/api.php`) the middleware group with the same name (ex. `api`) it will automatically be applied.

You can also apply middleware groups to routes or route groups:

```php
// given the above defined middleware groups

use App\Http\Controllers\UsersController;
use PikaJew002\Handrolled\Router\Definition\RouteGroup;

$route->group('/api', function(RouteGroup $route) {
    $route->get('/users', [UsersController::class, 'index'])->middleware('api');
    $route->group('/user', function(RouteGroup $route) {
        $route->post('', [UsersController::class, 'store']);
        $route->put('/{id:\d+}', [UsersController::class, 'update']);
    })->middleware('auth:api');
});
```

You can define and use your own middleware.

```php
use Exception;
use PikaJew002\Handrolled\Http\Exceptions\HttpException;
use PikaJew002\Handrolled\Http\Request;
use PikaJew002\Handrolled\Http\Responses\RedirectResponse;
use PikaJew002\Handrolled\Interfaces\Middleware;

class OtherMiddleware implements Middleware
{
    public function handler(Request $request, callable $next)
    {
        // on success/continue in middleware stack, continue to controller/route handler
        return $next($request);
        // or stop middleware processing and does not continue, return a response
        return new RedirectResponse('/login');
        // or throw an HttpException
        throw new HttpException(401, 'Unauthorized');
        // or any other exception
        throw new Exception('Something went wrong');
    }
}
```

Generic exceptions will throw a 500 Sever Error with no other message if application has debug mode set to false.
Otherwise, they will return an debug error page with the stack trace.

The body of the `HttpException` or `ServerErrorResponse` will be JSON by default (with Reponse header `Content-Type: application/json`) as Handrolled is an API first framework.

You can change the default behavior with the configuration option `app.response_type` in `config/app.php`.

```php
// {project_dir}/config/app.php

return [
    // ..
    // must be a valid mime-type
    // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Type
    'response_type' => 'text/html',
    // ..
];
```

<!-- Of course the type of `Response` instance returned from your controller will take precedence  -->

<!--

// @TODO pick up here

(`PikaJew002\Handrolled\Interfaces\Middleware`).

An example route file (e.x. `routes/api.php`) with can look something like this:

```php
// {project_dir}/routes/api.php
use PikaJew002\Handrolled\Http\Responses\JsonResponse;
use PikaJew002\Handrolled\Http\Responses\ViewResponse;
use PikaJew002\Handrolled\Router\Definition\RouteGroup;

// defines the prefix for the group
$route = new RouteGroup('/api');

$route->get('/', function() {
    return new ViewResponse('home.twig.html', ['variable' => 'This is some variable content']);
});

$routeGroup->route('GET', '/closure/optional-title[/{title}]', function($title = '', Request $request) {
    return new JsonResponse(['title' => $title]);
});

return $route;
```

Note: you can make and use your own middleware, just be sure it implements the middleware interface (`PikaJew002\Handrolled\Interfaces\Middleware`).

This routes file assumes a few things:

First, that you have created `UsersController`, `InvokableController`, `Auth\LoginController`, and `Auth\LogoutController` classes somewhere and are namespaced under `App\Http\Controllers`.
Second, that you have a template `resources/views/home.twig.html`.
Third, in order to use the `AuthenticateToken` middleware you will need to have a `User` model and a `Token` model (which the classes is defined in `config/auth.php`).

## User model

The `User` model must implement the interface and use the `Tokens` trait for `AuthenticateToken` middleware and the `Edibles` trait for `AuthenticateEdible` middleware:

```php
// {project_dir}/src/Models/User.php

namespace App\Models;

use PikaJew002\Handrolled\Database\Orm\Entity;
use PikaJew002\Handrolled\Interfaces\User as UserInterface;
use PikaJew002\Handrolled\Traits\Tokens;
use PikaJew002\Handrolled\Traits\Edibles;

class User extends Entity implements UserInterface
{
    use Edibles, Tokens;

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
}
```

This example model (or database entity) defines the table name and columns on the class and assumes the database already has this table created.

### Token model

The `Token` model must implement the interface:

```php
// {project_dir}/{project_src}/Models/Token.php

namespace App\Models\Token;

use PikaJew002\Handrolled\Interfaces\Token as TokenInterface;

class Token implements TokenInterface
{
    // ...
}

```

Along with a `UsersController.php` file:

```php
// {project_dir}/{project_src}/Http/Controllers/UsersController.php
namespace App\Http\Controllers;

use App\Models\User;
use PikaJew002\Handrolled\Http\Exceptions\HttpException;
use PikaJew002\Handrolled\Http\Responses\HttpErrors\NotFoundResponse;
use PikaJew002\Handrolled\Http\Responses\HttpErrors\ServerErrorResponse;
use PikaJew002\Handrolled\Http\Responses\JsonResponse;
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
        $user->password_hash = password_hash($request->input('password'), PASSWORD_DEFAULT);
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

        return new ServerErrorResponse('Database error! Could not delete user!');
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

# Frontend View Templates

The Twig templating engine is used to render `ViewResponse`s. For the example templates in `PikaJew002/handrolled-project`, TailwindCSS is used to add styling with CSS using the TailwindCSS CLI tool with JIT mode.

To start a watcher that will recompile when your templates change, run this:
```bash
npx tailwindcss -o public/css/app.css --watch --jit --purge="resources/views/**/*.twig.html"
```

This assumes you have `node` and `npm` installed on your machine. It will prompt you the first time to install the `tailwindcss` npm package (I assume globally).

To compile your CSS for production, run this:
```bash
NODE_ENV=production npx tailwindcss -o public/css/app.css --jit --purge="resources/views/**/*.twig.html" --minify
``` -->
