<?php

namespace GraylogLogger\Laravel\Providers;

use GraylogLogger\GraylogLogger;
use GraylogLogger\Laravel\GraylogLoggerHandler;
use Monolog\Logger;
use Illuminate\Log\Writer;
use Illuminate\Support\ServiceProvider;

/**
 * Class GraylogLoggerServiceProvider
 *
 * @package GraylogLogger\Laravel\Providers
 */
class GraylogLoggerServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     */
    public function register()
    {
        $this->app->bind('GraylogLogger', function ($app) {
            return new GraylogLogger(
                config('app.name'),
                config('app.env'),
                config('app.facility'),
                [],
                config('connections.default.host'),
                config('connections.default.port')
            );
        });
    }

    /**
     *
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/graylog_logger.php' => config_path('graylog_logger.php'),
        ]);

        $logger = $this->app['log'];

        if ($logger instanceof Writer) {
            $monolog = $logger->getMonolog();

            if ($monolog instanceof Logger) {
                $monolog->pushHandler(new GraylogLoggerHandler());
            }
        }
    }

    /**
     * @return array
     */
    public function provides()
    {
        return ['GraylogLogger'];
    }
}
