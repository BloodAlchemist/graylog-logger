<?php

namespace GraylogLogger\Processors;

use Gelf\Message;
use GraylogLogger\Serializers\Serializer;

/**
 * Interface Processor
 *
 * @package GraylogLogger\Processors
 */
interface Processor
{
    /**
     * Invoke processor on message.
     *
     * @param Message $message
     * @param Serializer $serializer
     * @return Message
     */
    public function __invoke(Message $message, Serializer $serializer);
}
