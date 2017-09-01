<?php

namespace GraylogLogger;

use Gelf\Message;
use Gelf\PublisherInterface;
use GraylogLogger\Processors\Processor;
use GraylogLogger\Serializers\Serializer;
use Psr\Log\InvalidArgumentException;

/**
 * Class GraylogLogger
 *
 * @package GraylogLogger
 */
class GraylogLogger
{
    /**
     * @var PublisherInterface
     */
    protected $publisher;

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var array
     */
    protected $processors;

    /**
     * Constructor.
     *
     * @param PublisherInterface|null $publisher
     * @param Serializer|null $serializer
     */
    public function __construct(PublisherInterface $publisher = null, Serializer $serializer = null)
    {
        $this->publisher = $publisher != null ? $this->setPublisher($publisher) : null;
        $this->serializer = $serializer != null ? $this->setSerializer($serializer) : null;
        $this->processors = [];
    }

    /**
     * Set publisher.
     *
     * @param PublisherInterface $publisher
     * @return $this
     */
    public function setPublisher(PublisherInterface $publisher)
    {
        if (!$publisher instanceof PublisherInterface) {
            throw new InvalidArgumentException("Wrong publisher argument");
        }

        $this->publisher = $publisher;
        return $this;
    }

    /**
     * Set serializer.
     *
     * @param Serializer $serializer
     * @return $this
     */
    public function setSerializer(Serializer $serializer)
    {
        if (!$serializer instanceof Serializer) {
            throw new InvalidArgumentException("Wrong serializer argument");
        }

        $this->serializer = $serializer;
        return $this;
    }

    /**
     * Push processor in processors stack.
     *
     * @param Processor $processor
     * @return $this
     */
    public function pushProcessor(Processor $processor)
    {
        if (!is_callable($processor)) {
            throw new InvalidArgumentException("Wrong processor argument");
        }

        array_unshift($this->processors, $processor);
        return $this;
    }

    /**
     * Pop processor from processors stack.
     *
     * @return Processor
     */
    public function popProcessor()
    {
        if (!count($this->processors)) {
            throw new \LogicException("Processor stack is empty");
        }

        return array_shift($this->processors);
    }

    /**
     * Publish GELF message
     *
     * @param Message $message
     */
    public function publish(Message $message)
    {
        if ($this->publisher == null) {
            throw new \LogicException("Publisher is not set");
        }

        if ($this->serializer == null) {
            throw new \LogicException("Serializer is not set");
        }

        if ($message) {
            // Processors perform GELF message
            if ($this->processors) {
                foreach ($this->processors as $processor) {
                    $message = call_user_func($processor, $message, $this->serializer);
                }
            }

            // Publish GELF message
            $this->publisher->publish($message);
        }
    }
}