<?php

return [

    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_values(array_filter([
        env('FRONTEND_URL'),
        'http://maya_logs.localhost',
        'https://maya_logs.localhost',
        'http://localhost:5173',
        'http://localhost:5174',
    ])),

    'allowed_origins_patterns' => [
        '#^https?://maya_logs\.localhost(:\d+)?$#',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => ['Content-Type', 'Cache-Control'],

    'max_age' => 0,

    'supports_credentials' => true,

];
