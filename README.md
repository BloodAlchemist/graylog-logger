# Graylog-Logger

PHP log handlers for integration with Graylog 2.

#### Support

* Laravel 5
* Yii 2

## Development Instructions

* For Laravel:
    * In config/app.php add to service providers:
    ```
    \GraylogLogger\Frameworks\Laravel\Providers\GraylogLoggerServiceProvider::class,
    ```
    * In config/app.php add to class aliases: 
    ```
    'GraylogLogger' => \GraylogLogger\Frameworks\Laravel\Facades\GraylogLoggerFacade::class
    ```
    * Create config/graylog_logger.php like:
    ```php
    <?php
  
    return [
        'name' => env('GRAYLOG_APP_NAME', env('APP_NAME', 'localhost')),
        'env' => env('GRAYLOG_APP_ENV', 'dev'),
        'facility' => env('GRAYLOG_APP_FACILITY', env('APP_NAME', 'localhost')),
        'host' => env('GRAYLOG_HOST', 'localhost'),
        'port' => env('GRAYLOG_PORT', 122201),
    ];
    ```
* For Yii 2:
    * Create config/graylog.php like:
    ```php
    <?php
    
    return [
        'class' => 'GraylogLogger\Frameworks\Yii2\GraylogTarget',
        'levels' => ['error', 'warning'],
        'host' => 'GRAYLOG_HOST',
        'port' => 12201,
        'name' => 'APP_NAME',
        'env' => 'APP_ENV',
        'facility' => 'local',
        'enabled' => true,
        'exportInterval' => 1
    ];

    ```
