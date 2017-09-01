<?php

namespace GraylogLogger\Processors;

use Gelf\Message;
use GraylogLogger\Serializers\Serializer;

/**
 * Class AppProcessor
 *
 * @package GraylogLogger\Processors
 */
class AppProcessor implements Processor
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $env;

    /**
     * @var string
     */
    protected $facility;

    /**
     * Constructor.
     *
     * @param string $name
     * @param string $env
     * @param string|null $facility
     */
    public function __construct($name, $env, $facility = null)
    {
        $this->name = $name;
        $this->env = $env;
        $this->facility = $facility == null ? $name : $facility;
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
        $message->setAdditional('app_name', $this->name);
        $message->setAdditional('app_env', $this->env);
        $message->setFacility($this->facility);
        return $message;
    }
}