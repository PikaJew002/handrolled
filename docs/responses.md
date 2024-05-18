# Responses

Responses that are returned from routes via `Closure` or controller method or from middleware must be a class instance that implements the `PikaJew002\Handrolled\Interfaces\Response` interface. Handrolled provides a "base" Response class (`PikaJew002\Handrolled\Http\Response`) out of the box as well as other derivatives for specific use cases.  

Reponses are made up of 3 essential parts:

 - a status code,
 - headers,
 - and a body

The status code must be a [valid HTTP response status code](https://developer.mozilla.org/en-US/docs/Web/HTTP/Status).
The headers must all be [valid HTTP reponse headers](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers).

# Reponse Body

The body of a Response can take a number of forms depending on the format or content type of the Response. For example, this could be a JSON encoded array, HTML, or an empty body. The `Content-Type` header is responsible for communicating to the client what type of Response body should be returned.

Handrolled provides a few basic types of Responses. As some Responses have dependencies, it is good practice to type hint the Response instance in the handler signature and then alter the Response as needed. In all of the examples of Responses in this documentation, we will be letting the container provide a Response instance to the controller method/`Closure`.

## View Response

The [`ViewResponse`](https://github.com/PikaJew002/handrolled/blob/main/src/Http/Responses/ViewResponse.php) class will by default set the status code to `200` and include a `Content-Type` header with a value of `text/html`. The body will be a compiled Twig template.
This Response should be type hinted as a dependency in the handler signature. By letting the container provide a `ViewResponse` instance, the controller doesn't need to worry about the details required to instantiate the class. This is because it needs the Twig environment to load a template from a file, compile the template with dynamic props provided, and set the Response body to this rendered output. To use a template, call the `use` method on the Response with the template name and props which returns the Response instance.
It can be used like so:

```php
// {project_dir}/routes/web.php
use PikaJew002\Handrolled\Http\Responses\ViewResponse;
use PikaJew002\Handrolled\Interfaces\Response as ResponseInterface;

$route->get('/items', function(ViewResponse $response): ResponseInterface {
    $title = 'Index Page';
    $items = ['first_item', 'second_item'];
    // assuming the template at {project_dir}/resources/views/index.twig.html exists
    return $response->use('index.twig.html', ['title' => $title, 'items' => $items]);
});
```

The setup for the Twig environment is handled in the `PikaJew002\Handrolled\Application\Services\ViewService` class.

You can read more about Twig templates in the [Twig documentation](https://twig.symfony.com/doc/3.x/).

## JSON Response

The [`JsonResponse`](https://github.com/PikaJew002/handrolled/blob/main/src/Http/Responses/JsonResponse.php) class will by default set the status code to `200` and include the headers `Content-Type: application/json` and `Cache-Control: no-cache, private`. The body, passed to the `with` method, which returns the instance, should be an array that will be JSON encoded.

 ```php
 // {project_dir}/routes/web.php
use PikaJew002\Handrolled\Http\Responses\JsonResponse;
use PikaJew002\Handrolled\Interfaces\Response as ResponseInterface;

$route->get('/items', function(JsonResponse $response): ResponseInterface {
    $title = 'Index Page';
    $items = ['first_item', 'second_item'];
    // assuming the template at {project_dir}/resources/views/index.twig.html exists
    return $response->with(['title' => $title, 'items' => $items]);
});
 ```

## Redirect Response

The [`RedirectResponse`](https://github.com/PikaJew002/handrolled/blob/main/src/Http/Responses/RedirectResponse.php) class will by default set the status code to `303` and include the `Location` header.
It can be used like:

```php
use PikaJew002\Handrolled\Http\Responses\RedirectResponse;
use PikaJew002\Handrolled\Interfaces\Response as ResponseInterface;

// using relative URL, assumes route GET '/other' exists in the application
// redirects to {app_url}/other
$route->get('/redirect', fn(RedirectResponse $response): ResponseInterface => $response->to('/other'));

// using absolute URL
// redirects to https://google.com
$route->get('/redirect', fn(RedirectResponse $response): ResponseInterface => $response->to('https://google.com'));
```

The value of the `Location` header is set to the URL passed to the `to` method.

The status code can be set to another [redirect code](https://developer.mozilla.org/en-US/docs/Web/HTTP/Redirections) using the fluent `setCode` method on the Response.

```php
use PikaJew002\Handrolled\Http\Responses\RedirectResponse;
use PikaJew002\Handrolled\Interfaces\Response as ResponseInterface;

// assumes route GET '/other' exists in the application
// redirects to '{app_url}/other'
$route->get('/redirect', fn(RedirectResponse $response): ResponseInterface => $response->setCode(308)->to('/other'));
```

It is considered best practice to redirect using HTTP (with the appropriate `3xx` status code). However, if you set the status to a `2xx` code, the redirect will still occur.
This is done by setting the `Content-Type: text/html` header and the body to:

```php
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <meta http-equiv="refresh" content="0;url='{$url}'" />
        <title>Redirecting to {$url}</title>
    </head>
    <body>
        Redirecting to <a href="{$url}">{$url}</a>.
    </body>
</html>
```

Where `{$url}` is the value passed to the `to` method. This ensures the redirect happens even if the HTTP status code is not a `3xx` code by way of the HTML `<meta>` element.

## HTTP Error Response

If there is a need to return a Response with a specific status code, Handrolled provides [Response classes](https://github.com/PikaJew002/handrolled/tree/main/src/Http/Responses/HttpErrors) for a number of the most commonly use eorror status codes (`4xx` or `5xx`).

 - `400`: [`BadRequestResponse`](https://github.com/PikaJew002/handrolled/blob/main/src/Http/Responses/HttpErrors/BadRequestResponse.php)
    - An incorrectly formatted Request or simply not what was expected, should always send a descriptive error message back with the Response so the user knows what the expected format of the Request should be, is often used to indicate errors in a form submission
 - `401`: [`UnauthorizedResponse`](https://github.com/PikaJew002/handrolled/blob/main/src/Http/Responses/HttpErrors/UnauthorizedResponse.php)
    - Request was made to a URL/route that requires the user/client to be authenticated to access, commonly used for letting a user know they need to login/authentication is needed to attempt to access the resource
 - `403`: [`ForbiddenResponse`](https://github.com/PikaJew002/handrolled/blob/main/src/Http/Responses/HttpErrors/ForbiddenResponse.php)
    - Request was made to a URL/route the user/client does not have proper authorization to access, commonly used for letting a user who is already logged in know that they do not have the proper permissions to access the resource
 - `404`: [`NotFoundResponse`](https://github.com/PikaJew002/handrolled/blob/main/src/Http/Responses/HttpErrors/NotFoundResponse.php)
    - Request was made to a URL/route or resource that does not exist, the framework uses this Response to indictate the route being requested does not exist or the developer may use this Response to indictate a resource is not available at the URL/route requested
 - `405`: [`MethodNotAllowedResponse`](https://github.com/PikaJew002/handrolled/blob/main/src/Http/Responses/HttpErrors/MethodNotAllowedResponse.php)
    - Request was made to a URL/route that exists, but using the wrong HTTP method, primarily only used by the framework, sets the `Allow` HTTP Response header to a list of HTTP methods (comma delimited) that are allowed for the URL/route requested
 - `500`: [`ServerErrorResponse`](https://github.com/PikaJew002/handrolled/blob/main/src/Http/Responses/HttpErrors/ServerErrorResponse.php)
    - An unexpected server error occured, will be used in the case of an uncaught/unhandled exception when `app.debug` is set to `false`

If there is a need for a reusable Response class that is in the `4xx` to `5xx` range of HTTP status codes that does not already exist, make a class that extends the [`HttpErrorResponse`](https://github.com/PikaJew002/handrolled/blob/main/src/Http/Responses/HttpErrorResponse.php) class like so:

```php

namespace App\Http\Responses\HttpErrors;

use PikaJew002\Handrolled\Http\Request;
use PikaJew002\Handrolled\Http\Responses\HttpErrorResponse;
use PikaJew002\Handrolled\Support\Configuration;
use Twig\Environment as TwigEnvironment;

class RequestTimeoutResponse extends HttpErrorResponse
{
    public function __construct(?string $message = null, array $headers = [], Request $request, Configuration $config, TwigEnvironment $twig)
    {
        parent::__construct(
            408,
            $message ?? 'Request Timeout',
            array_merge(['Connection' => 'close'], $headers),
            $request, $config, $twig);
    }
}
```



The body of the `HttpException` or `ServerErrorResponse` will be JSON by default (with Response header `Content-Type: application/json`) as Handrolled is an API first framework.

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
