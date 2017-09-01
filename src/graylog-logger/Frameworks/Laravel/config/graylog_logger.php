<?php

return [
    'host' => env('GRAYLOG_HOST', 'localhost'),
    'port' => env('GRAYLOG_PORT', 122201),

    'name' => env('GRAYLOG_APP_NAME', env('APP_NAME', 'localhost')),
    'env' => env('GRAYLOG_APP_ENV', 'dev'),

    'facility' => env('GRAYLOG_APP_FACILITY', env('APP_NAME', 'localhost')),
];
