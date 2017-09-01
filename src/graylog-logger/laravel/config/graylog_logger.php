<?php

return [
    'app' => [
        'name' => env('GRAYLOG_APP_NAME', env('APP_NAME', 'localhost')),
        'env' => env('GRAYLOG_APP_ENV', 'dev'),
        'facility' => env('GRAYLOG_APP_FACILITY', env('APP_NAME', 'localhost')),
    ],
    'connections' => [
        'default' => [
            'host' => 'localhost',
            'port' => 122201,
        ]
    ]
];
