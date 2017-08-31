<?php

namespace GraylogLogger;

/**
 * Class Serializer
 * This class is based on code from Sentry Raven serializer.
 *
 * @package GraylogLogger
 */
class Serializer
{
    /*
     * The default mb detect order.
     */
    const DEFAULT_MB_DETECT_ORDER = 'auto';

    /*
     * Suggested detect order for western countries
     */
    const WESTERN_MB_DETECT_ORDER = 'UTF-8, ASCII, ISO-8859-1, ISO-8859-2, ISO-8859-3, ISO-8859-4, ISO-8859-5, ISO-8859-6, ISO-8859-7, ISO-8859-8, ISO-8859-9, ISO-8859-10, ISO-8859-13, ISO-8859-14, ISO-8859-15, ISO-8859-16, Windows-1251, Windows-1252, Windows-1254';

    /**
     * This is the default mb detect order for the detection of encoding.
     *
     * @var string
     */
    protected $mb_detect_order = self::DEFAULT_MB_DETECT_ORDER;

    /**
     * Constructor.
     *
     * @param null|string $mb_detect_order
     */
    public function __construct($mb_detect_order = null)
    {
        if ($mb_detect_order != null) {
            $this->mb_detect_order = $mb_detect_order;
        }
    }

    /**
     * Serialize an object (recursively) into something safe for data sanitization and encoding.
     *
     * @param mixed $value
     * @param int   $max_depth
     * @param int   $_depth
     * @return string|bool|double|int|null|object|array
     */
    public function serialize($value, $max_depth = 3, $_depth = 0)
    {
        $className = is_object($value) ? get_class($value) : null;
        $toArray = is_array($value) || $className === 'stdClass';

        if ($toArray && $_depth < $max_depth) {
            $new = array();

            foreach ($value as $k => $v) {
                $new[$this->serializeValue($k)] = $this->serialize($v, $max_depth, $_depth + 1);
            }

            return $new;
        }

        return $this->serializeValue($value);
    }

    /**
     * Serialize value.
     *
     * @param mixed $value
     * @return string|bool|double|int|null
     */
    protected function serializeValue($value)
    {
        if ($value === null) {
            return 'null';
        } elseif ($value === false) {
            return 'false';
        } elseif ($value === true) {
            return 'true';
        } elseif (is_float($value) && (int) $value == $value) {
            return $value.'.0';
        } elseif (is_integer($value) || is_float($value)) {
            return (string) $value;
        } elseif (is_object($value) || gettype($value) == 'object') {
            return 'Object '.get_class($value);
        } elseif (is_resource($value)) {
            return 'Resource '.get_resource_type($value);
        } elseif (is_array($value)) {
            return 'Array of length ' . count($value);
        } else {
            return $this->serializeString($value);
        }
    }

    /**
     * Serialize string.
     *
     * @param $value
     * @return mixed|string
     */
    protected function serializeString($value)
    {
        $value = (string) $value;
        if (function_exists('mb_detect_encoding')
            && function_exists('mb_convert_encoding')
        ) {
            // we always guarantee this is coerced, even if we can't detect encoding
            if ($currentEncoding = mb_detect_encoding($value, $this->mb_detect_order)) {
                $value = mb_convert_encoding($value, 'UTF-8', $currentEncoding);
            } else {
                $value = mb_convert_encoding($value, 'UTF-8');
            }
        }

        if (strlen($value) > 1024) {
            $value = substr($value, 0, 1014) . ' {clipped}';
        }

        return $value;
    }
}