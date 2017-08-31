<?php

namespace GraylogLogger;

use Gelf\Message;
use Gelf\Publisher;
use Gelf\Transport\IgnoreErrorTransportWrapper;
use Gelf\Transport\TcpTransport;
use Psr\Log\LogLevel;

/**
 * Class GraylogLogger
 *
 * @package GraylogLogger
 */
class GraylogLogger
{
    /**
     * @var Publisher
     */
    protected $publisher;

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var array
     */
    protected $exclude;

    /**
     * @var array
     */
    protected $levels;

    /**
     * @var string
     */
    protected $facility;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $env;

    /**
     * @var array
     */
    protected $extraFromServer = [
        'supervisor_process_name' => 'SUPERVISOR_PROCESS_NAME',
        'request_method' => 'REQUEST_METHOD',
        'remote_address' => 'REMOTE_ADDR',
        'request_url' => 'REQUEST_URI',
        'referrer' => 'HTTP_REFERER',
        'server' => 'SERVER_NAME',
        'query' => 'QUERY_STRING',
    ];

    /**
     * Graylog constructor.
     *
     * @param string $name
     * @param string $env
     * @param string $facility
     * @param array $levels
     * @param string $host
     * @param int $port
     */
    public function __construct($name, $env, $facility, $levels = [], $host = '127.0.0.1', $port = 122201)
    {
        $this->name = $name;
        $this->env = $env;
        $this->facility = $facility;
        $this->levels = $levels;

        // Set serializer
        $this->serializer = new Serializer();

        // Set publisher
        $this->publisher = new Publisher(
            new IgnoreErrorTransportWrapper(
                new TcpTransport($host, $port)
            )
        );
    }

    /**
     * Set levels links.
     *
     * @param array $levels
     * @return $this
     * @throws \Exception
     */
    public function setLevels($levels)
    {
        if (!is_array($levels)) {
            throw new \Exception('Wrong level param, must be array');
        }

        $this->levels = $levels;
        return $this;
    }

    /**
     * Set exclude.
     *
     * @param array|string $exclude
     * @return $this
     * @throws \Exception
     */
    public function setExclude($exclude)
    {
        if (is_array($exclude)) {
            $this->exclude = $exclude;
        } elseif (is_string($exclude)) {
            $this->exclude = [$exclude];
        } else {
            throw new \Exception('Wrong exclude param');
        }

        return $this;
    }

    /**
     * Publish message.
     *
     * @param $message
     */
    public function publish($message)
    {
        // Check exclude list
        if (!in_array(get_class($message), $this->exclude)) {
            // Get GELF and publish
            $this->publisher->publish($this->getGelfMessage($message));
        }
    }

    /**
     * Get GELF message from Exception.
     *
     * @param \Exception $message
     * @return Message
     */
    protected function getGelfMessage($message)
    {
        // Assigns a list of variables
        list($body, $level, $category, $timestamp) = $message;

        // Prepare base message
        $gelf = new Message();
        $gelf->setLevel(Utils::getValue($this->levels, $level, LogLevel::INFO))
            ->setAdditional('category', $category)
            ->setFacility($this->facility)
            ->setTimestamp($timestamp)
            ->setFile('unknown')
            ->setLine(0);

        if (is_string($body)) {
            // For string log message set only shortMessage
            $gelf->setShortMessage($body);

        } elseif ($body instanceof \Exception) {
            // For Exception family set fields
            $gelf->setShortMessage('Exception ' . get_class($body) . ': ' . $body->getMessage())
                ->setFullMessage((string)$body)
                ->setLine($body->getLine())
                ->setFile($body->getFile());

        } else {
            // For else variant, if log message contains special keys 'short', 'full' or 'add',
            // will use them as shortMessage, fullMessage and additionals respectively

            // If 'short' is set
            if ($short = Utils::removeValue($text, 'short') !== null) {
                $gelf->setShortMessage($short);
                // All remaining message is fullMessage by default
                $gelf->setFullMessage($this->serializer->serialize($text));
            } else {
                // Will use log message as shortMessage by default (no need to add fullMessage in this case)
                $gelf->setShortMessage($this->serializer->serialize($text));
            }

            // If 'full' is set will use it as fullMessage (note that all other stuff in log message will not be logged,
            // except 'short' and 'add')
            if ($full = Utils::removeValue($text, 'full') !== null) {
                $gelf->setFullMessage($this->serializer->serialize($full));
            }

            // Process additionals array (only with string keys)
            if ($add = Utils::removeValue($text, 'add') !== null) {
                if (is_array($add)) {
                    foreach ($add as $key => $val) {
                        if (is_string($key)) {
                            if (!is_string($val)) {
                                $val = $this->serializer->serialize($val);
                            }
                            $gelf->setAdditional($key, $val);
                        }
                    }
                }
            }
        }

        // Set 'file', 'line' and additional 'trace', if log message contains traces array
        if (isset($message[4]) && is_array($message[4])) {
            $traces = [];
            foreach ($message[4] as $index => $trace) {
                $traces[] = "{$trace['file']}:{$trace['line']}";
                if ($index === 0) {
                    $gelf->setFile($trace['file'])
                        ->setLine($trace['line']);
                }
            }
            $gelf->setAdditional('trace', implode("\n", $traces));
        }

        // Add extra
        foreach ($this->getExtraFields() as $key => $value) {
            $gelf->setAdditional($key, $value);
        }

        return $gelf;
    }

    /**
     * Get extra fields.
     *
     * @return array
     */
    private function getExtraFields()
    {
        // Default fields
        $extra = [
            'app_name' => $this->name,
            'app_env' => $this->env,
            'url' => Utils::getCurrentRequestUrl()
        ];

        // Add from $_SERVER
        foreach ($this->extraFromServer as $extraName => $serverName) {
            if (isset($_SERVER[$serverName])) {
                $extra[$extraName] = $_SERVER[$serverName];
            }
        }

        // Session id
        if (function_exists('session_id') && ($id = session_id())) {
            $extra['session_id'] = $id;
        }

        // Add globals
        $extra['get'] = print_r($this->serializer->serialize($_GET), true);
        $extra['post'] = print_r($this->serializer->serialize($_POST), true);
        $extra['files'] = print_r($this->serializer->serialize($_FILES), true);
        $extra['session'] = print_r($this->serializer->serialize($_SESSION), true);

        return $extra;
    }
}