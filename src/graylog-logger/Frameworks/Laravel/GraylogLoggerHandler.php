<?php

namespace GraylogLogger\Frameworks\Laravel;

use GraylogLogger\GraylogLogger;
use Monolog\Handler\AbstractHandler;
use Monolog\Formatter\GelfMessageFormatter;

/**
 * Class GraylogLoggerHandler
 *
 * @package GraylogLogger\Laravel
 */
class GraylogLoggerHandler extends AbstractHandler
{
    /**
     * @var GelfMessageFormatter
     */
    protected $messageFormatter;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->messageFormatter = new GelfMessageFormatter();
    }

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
                $graylog->publish($this->messageFormatter->format($message));
            }
        }
    }
}
