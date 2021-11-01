<?php

use PikaJew002\Handrolled\Application\Services;

return [
    'name' => env('APP_NAME', 'Handrolled'),
    'url' => env('APP_URL', 'http://localhost'),
    'env' => env('APP_ENV', 'production'),
    'debug' => env('APP_DEBUG', false),
    'paths' => [
        'project' => env('APP_PATH', 'tests/artifacts'),
        'routes' => env('APP_ROUTES', 'routes'),
        'views' => env('APP_VIEWS', 'resources/views'),
        'cache' => env('APP_CACHE', 'boot/cache'),
    ],
    'services' => [
        Services\ApplicationService::class,
        Services\DatabaseService::class,
        Services\AuthService::class,
        Services\RouteService::class,
        Services\ViewService::class,
    ],
];
