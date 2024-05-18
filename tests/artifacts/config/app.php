<?php

use PikaJew002\Handrolled\Application\Services;

return [
    'name' => env('APP_NAME', 'Handrolled'),
    'encryption_key' => env('APP_ENC_KEY', null),
    'url' => env('APP_URL', 'http://localhost'),
    'env' => env('APP_ENV', 'testing'),
    'debug' => env('APP_DEBUG', false),
    'response_type' => 'application/json',
    'paths' => [
        'project' => env('APP_PATH', 'tests/artifacts'),
        'routes' => env('APP_ROUTES', 'routes'),
        'views' => env('APP_VIEWS', 'resources/views'),
        'handrolled_views' => env('HANDROLLED_VIEWS', '../../src/Support/views'),
        'cache' => env('APP_CACHE', 'boot/cache'),
    ],
    'services' => [
        Services\DatabaseService::class,
        Services\AuthService::class,
        Services\RouteService::class,
        Services\ViewService::class,
    ],
];
