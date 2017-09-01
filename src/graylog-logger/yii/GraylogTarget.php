<?php

namespace GraylogLogger;

use yii\log\Logger;
use yii\log\Target;
use Psr\Log\LogLevel;

/**
 * Class GraylogTarget
 *
 * @package GraylogLogger
 */
class GraylogTarget extends Target
{
    /**
     * @var string Graylog host
     */
    public $host = "127.0.0.1";

    /**
     * @var int Graylog port
     */
    public $port = 12201;

    /**
     * @var string Default facility name
     */
    public $facility = "yii2-logs";

    /**
     * @var string Default app name
     */
    public $name = "";

    /**
     * @var string Default app env
     */
    public $env = "";

    /**
     * @var array Graylog levels
     */
    private $levels = [
        Logger::LEVEL_TRACE => LogLevel::DEBUG,
        Logger::LEVEL_PROFILE_BEGIN => LogLevel::DEBUG,
        Logger::LEVEL_PROFILE_END => LogLevel::DEBUG,
        Logger::LEVEL_INFO => LogLevel::INFO,
        Logger::LEVEL_WARNING => LogLevel::WARNING,
        Logger::LEVEL_ERROR => LogLevel::ERROR,
    ];

    /**
     * Exports log [[messages]] to a Graylog2 destination.
     */
    public function export()
    {
        $graylog = new GraylogLogger($this->name, $this->env, $this->facility, $this->levels, $this->host, $this->port);
        foreach ($this->messages as $message) {
            $graylog->publish($message);
        }
    }
}
