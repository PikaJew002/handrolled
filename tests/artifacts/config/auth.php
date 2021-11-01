<?php

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
        'token' => [
            'class' => \App\Models\Token::class,
            'length' => 3600, // 1 hr
        ],
    ],
];
