<?php

namespace GraylogLogger\Laravel;

use GraylogLogger\GraylogLogger;
use Monolog\Handler\AbstractHandler;

/**
 * Class GraylogLoggerHandler
 *
 * @package GraylogLogger\Laravel
 */
class GraylogLoggerHandler extends AbstractHandler
{
    /**
     * Handle the log messages.
     *
     * @param array $messages
     */
    public function handle($messages)
    {
        /**
         * @var GraylogLogger $graylog
         */
        if ($graylog = app('GraylogLogger') != null) {
            foreach ($messages as $message) {
                $graylog->publish($message);
            }
        }
    }
}
