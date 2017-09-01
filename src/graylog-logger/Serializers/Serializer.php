<?php

namespace GraylogLogger\Serializers;

/**
 * Interface Serializer
 *
 * @package GraylogLogger\Serializers
 */
interface Serializer
{
    /**
     * Serialize an object (recursively) into something safe for data sanitization and encoding.
     *
     * @param mixed $value
     * @param int $max_depth
     * @param int $_depth
     * @return string|bool|double|int|null|object|array
     */
    public function serialize($value, $max_depth = 3, $_depth = 0);
}
