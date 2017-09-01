<?php

namespace GraylogLogger\Processors;

use Gelf\Message;
use GraylogLogger\Serializers\Serializer;

/**
 * Class WebServerProcessor
 *
 * @package GraylogLogger\Processors
 */
class WebServerProcessor implements Processor
{
    /**
     * @var array Default server fields
     */
    protected $extraFields = [
        'server' => 'SERVER_NAME',
        'supervisor_process_name' => 'SUPERVISOR_PROCESS_NAME',
        'request_url' => 'REQUEST_URI',
        'request_method' => 'REQUEST_METHOD',
        'query' => 'QUERY_STRING',
        'remote_address' => 'REMOTE_ADDR',
        'unique_id' => 'UNIQUE_ID',
    ];

    /**
     * Constructor.
     *
     * @param array|null $extraFields
     */
    public function __construct($extraFields = null)
    {
        // Ser extra fields
        if ($extraFields != null) {
            if (is_array($extraFields)) {
                $this->extraFields = array_unique(array_merge($this->extraFields, $extraFields));
            } else {
                throw new \InvalidArgumentException("Wrong extra fields argument");
            }
        }
    }

    /**
     * Invoke processor on message.
     *
     * @param Message $message
     * @param Serializer $serializer
     * @return Message
     */
    public function __invoke(Message $message, Serializer $serializer)
    {
        // Get current url
        $message->setAdditional('url', $this->getCurrentUrl());

        // Add extra server data
        foreach ($this->extraFields as $extra => $server) {
            if (isset($_SERVER[$server])) {
                $message->setAdditional($extra, $_SERVER[$server]);
            }
        }

        // Add headers
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $header_key = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                $message->setAdditional($header_key, $value);
            } elseif (in_array($key, array('CONTENT_TYPE', 'CONTENT_LENGTH')) && $value !== '') {
                $header_key = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', $key))));
                $message->setAdditional($header_key, $value);
            }
        }

        // Add other globals
        $message->setAdditional('get', print_r($serializer->serialize($_GET), true));
        $message->setAdditional('post', print_r($serializer->serialize($_POST), true));
        $message->setAdditional('files', print_r($serializer->serialize($_FILES), true));
        $message->setAdditional('session', print_r($serializer->serialize($_SESSION), true));

        // Session id
        if (function_exists('session_id') && ($id = session_id())) {
            $extra['session_id'] = $id;
        }

        return $message;
    }

    /**
     * Get current url.
     *
     * @return null|string
     */
    protected function getCurrentUrl()
    {
        if (!isset($_SERVER['REQUEST_URI'])) {
            return null;
        }

        $host = !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : (
        !empty($_SERVER['LOCAL_ADDR']) ? $_SERVER['LOCAL_ADDR'] : (
        !empty($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : ''));

        $protocol = self::isHttps() ? 'https' : 'http';
        return "{$protocol}://{$host}{$_SERVER['REQUEST_URI']}";
    }

    /**
     * Is https request.
     *
     * @param bool $trust_x_forwarded_proto
     * @return bool
     */
    protected function isHttps($trust_x_forwarded_proto = true)
    {
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            return true;
        }

        if (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) {
            return true;
        }

        if (!empty($trust_x_forwarded_proto) && !empty($_SERVER['X-FORWARDED-PROTO'])
            && $_SERVER['X-FORWARDED-PROTO'] === 'https'
        ) {
            return true;
        }

        return false;
    }
}
