<?php

namespace GraylogLogger;

/**
 * Class Utils
 * This class is based on code from Yii2 helpers.
 *
 * @package GraylogLogger
 */
class Utils
{
    /**
     * Get current url.
     *
     * @return null|string
     */
    public static function getCurrentRequestUrl()
    {
        if (!isset($_SERVER['REQUEST_URI'])) {
            return null;
        }

        $host = !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : (
                    !empty($_SERVER['LOCAL_ADDR']) ? $_SERVER['LOCAL_ADDR'] : (
                        !empty($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : ''
                    )
        );

        $protocol = self::isHttps() ? 'https' : 'http';
        return "{$protocol}://{$host}{$_SERVER['REQUEST_URI']}";
    }

    /**
     * Is https request.
     *
     * @param bool $trust_x_forwarded_proto
     * @return bool
     */
    public static function isHttps($trust_x_forwarded_proto = true)
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

    /**
     * Retrieves the value of an array element or object property with the given key or property name.
     * Copy from \yii\helpers\BaseArrayHelper.
     *
     * @param array|object $array
     * @param string|\Closure|array $key
     * @param mixed $default
     * @return mixed|null
     */
    public static function getValue($array, $key, $default = null)
    {
        if ($key instanceof \Closure) {
            return $key($array, $default);
        }

        if (is_array($key)) {
            $lastKey = array_pop($key);
            foreach ($key as $keyPart) {
                $array = static::getValue($array, $keyPart);
            }
            $key = $lastKey;
        }

        if (is_array($array) && (isset($array[$key]) || array_key_exists($key, $array))) {
            return $array[$key];
        }

        if (($pos = strrpos($key, '.')) !== false) {
            $array = static::getValue($array, substr($key, 0, $pos), $default);
            $key = substr($key, $pos + 1);
        }

        if (is_object($array)) {
            return $array->$key;
        } elseif (is_array($array)) {
            return (isset($array[$key]) || array_key_exists($key, $array)) ? $array[$key] : $default;
        }

        return $default;
    }

    /**
     * Removes an item from an array and returns the value.
     * If the key does not exist in the array, the default value will be returned instead.
     * Copy from \yii\helpers\BaseArrayHelper.
     *
     * @param array $array
     * @param string $key
     * @param mixed|null $default
     * @return mixed|null
     */
    public static function removeValue(&$array, $key, $default = null)
    {
        if (is_array($array) && (isset($array[$key]) || array_key_exists($key, $array))) {
            $value = $array[$key];
            unset($array[$key]);

            return $value;
        }

        return $default;
    }
}
