<?php

namespace GraylogLogger\Frameworks\Laravel\Providers;

use Gelf\Publisher;
use Gelf\Transport\IgnoreErrorTransportWrapper;
use Gelf\Transport\TcpTransport;
use GraylogLogger\GraylogLogger;
use GraylogLogger\Laravel\GraylogLoggerHandler;
use GraylogLogger\Processors\AppProcessor;
use GraylogLogger\Serializers\TypeSerializer;
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
            return (new GraylogLogger())
                ->setPublisher(new Publisher(new IgnoreErrorTransportWrapper(new TcpTransport(
                    config('graylog_logger.host'),
                    config('graylog_logger.port')
                ))))
                ->setSerializer(new TypeSerializer())
                ->pushProcessor(new AppProcessor(
                    config('graylog_logger.name'),
                    config('graylog_logger.env'),
                    config('graylog_logger.facility')
                ))
                ->pushProcessor(new WebServerProcessor());
        });
    }

    /**
     * Boot.
     */
    public function boot()
    {
        // Set publishes
        $this->publishes([__DIR__ . '/config/graylog_logger.php' => config_path('graylog_logger.php')]);

        // Set handler
        if (($logger = $this->app['log']) instanceof Writer) {
            if (($monolog = $logger->getMonolog()) instanceof Logger) {
                $monolog->pushHandler(new GraylogLoggerHandler());
            }
        }
    }

    /**
     * Provides.
     *
     * @return array
     */
    public function provides()
    {
        return ['GraylogLogger'];
    }
}
