<?php

namespace GraylogLogger\Frameworks\Yii2;

use Gelf\Message;
use Psr\Log\LogLevel;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;
use yii\log\Logger;

/**
 * Class GelfMessageFormatter
 *
 * @package GraylogLogger\Frameworks\Yii2
 */
class GelfMessageFormatter
{
    /**
     * @var array Translates Yii2 log levels to Graylog2 log priorities.
     */
    private $logLevels = [
        Logger::LEVEL_TRACE => LogLevel::DEBUG,
        Logger::LEVEL_PROFILE_BEGIN => LogLevel::DEBUG,
        Logger::LEVEL_PROFILE_END => LogLevel::DEBUG,
        Logger::LEVEL_INFO => LogLevel::INFO,
        Logger::LEVEL_WARNING => LogLevel::WARNING,
        Logger::LEVEL_ERROR => LogLevel::ERROR,
    ];

    /**
     * Format record to GEFL message.
     *
     * @param $record
     * @return Message
     */
    public function format($record)
    {
        // Assigns a list of variables
        list($body, $level, $category, $timestamp) = $record;

        // Prepare base message
        /** @var Message $gelf */
        $gelf = (new Message())
            ->setLevel(ArrayHelper::getValue($this->logLevels, $level, LogLevel::INFO))
            ->setAdditional('category', $category)
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
            if ($short = ArrayHelper::remove($body, 'short') !== null) {
                $gelf->setShortMessage($short);
                // All remaining message is fullMessage by default
                $gelf->setFullMessage(VarDumper::dumpAsString($body));
            } else {
                // Will use log message as shortMessage by default (no need to add fullMessage in this case)
                $gelf->setShortMessage(VarDumper::dumpAsString($body));
            }

            // If 'full' is set will use it as fullMessage (note that all other stuff in log message will not be logged,
            // except 'short' and 'add')
            if ($full = ArrayHelper::remove($body, 'full') !== null) {
                $gelf->setFullMessage(VarDumper::dumpAsString($full));
            }

            // Process additionals array (only with string keys)
            if ($add = ArrayHelper::remove($body, 'add') !== null) {
                if (is_array($add)) {
                    foreach ($add as $key => $val) {
                        if (is_string($key)) {
                            if (!is_string($val)) {
                                $val = VarDumper::dumpAsString($val);
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

        return $gelf;
    }
}