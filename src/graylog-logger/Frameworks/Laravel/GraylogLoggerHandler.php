<?php

namespace GraylogLogger\Frameworks\Laravel;

use Monolog\Handler\AbstractHandler;
use Monolog\Formatter\GelfMessageFormatter;
use Monolog\Logger;

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
     *
     * @param int $level
     * @param bool $bubble
     */
    public function __construct($level = Logger::DEBUG, $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->messageFormatter = new GelfMessageFormatter();
    }

    /**
     * Handles a record.
     *
     * All records may be passed to this method, and the handler should discard
     * those that it does not want to handle.
     *
     * The return value of this function controls the bubbling process of the handler stack.
     * Unless the bubbling is interrupted (by returning true), the Logger class will keep on
     * calling further handlers in the stack with a given log record.
     *
     * @param  array $record The record to handle
     * @return Boolean true means that this handler handled the record, and that bubbling is not permitted.
     *                        false means the record was either not processed or that this handler allows bubbling.
     */
    public function handle(array $record)
    {
        if (($graylog = app('GraylogLogger')) != null) {
            $graylog->publish($this->messageFormatter->format($record));
            return true;
        }

        return false;
    }
}
