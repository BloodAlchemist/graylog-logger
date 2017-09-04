<?php

namespace GraylogLogger\Frameworks\Yii2;

use Gelf\Publisher;
use Gelf\Transport\IgnoreErrorTransportWrapper;
use Gelf\Transport\TcpTransport;
use GraylogLogger\GraylogLogger;
use GraylogLogger\Processors\AppProcessor;
use GraylogLogger\Processors\WebServerProcessor;
use GraylogLogger\Serializers\TypeSerializer;
use yii\log\Logger;
use yii\log\Target;

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
     * Exports log [[messages]] to a Graylog2 destination.
     */
    public function export()
    {
        $graylog = (new GraylogLogger())
            ->setPublisher(new Publisher(new IgnoreErrorTransportWrapper(new TcpTransport($this->host, $this->port))))
            ->setSerializer(new TypeSerializer())
            ->pushProcessor(new AppProcessor($this->name, $this->env, $this->facility))
            ->pushProcessor(new WebServerProcessor());
        $messageFormatter = new GelfMessageFormatter();

        foreach ($this->messages as $message) {
            $graylog->publish($messageFormatter->format($message));
        }
    }
}
