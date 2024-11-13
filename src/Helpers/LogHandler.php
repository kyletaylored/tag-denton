<?php

namespace App\Helpers;

use Monolog\Logger;
use Monolog\Level;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\ErrorLogHandler;

class LogHandler
{
    /**
     * The Monolog logger instance.
     *
     * @var Logger
     */
    private static $logger;

    /**
     * Initialize the logger if not already initialized.
     *
     * @return Logger
     */
    private static function getLogger(): Logger
    {
        if (!self::$logger) {
            // Create a new logger instance
            self::$logger = new Logger('tag-denton');

            // Use a writable path like /tmp for logs
            $logFile = '/tmp/tag-denton-app.log';

            // Add a StreamHandler to log to the writable path
            self::$logger->pushHandler(new StreamHandler($logFile, Level::Debug));

            // Add an ErrorLogHandler to log to stderr (Docker-compatible)
            self::$logger->pushHandler(new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, Level::Debug));
        }

        return self::$logger;
    }

    /**
     * Log an INFO level message.
     *
     * @param string $message
     * @param array $context
     */
    public static function info(string $message, array $context = []): void
    {
        self::getLogger()->info($message, $context);
    }

    /**
     * Log a WARNING level message.
     *
     * @param string $message
     * @param array $context
     */
    public static function warning(string $message, array $context = []): void
    {
        self::getLogger()->warning($message, $context);
    }

    /**
     * Log an ERROR level message.
     *
     * @param string $message
     * @param array $context
     */
    public static function error(string $message, array $context = []): void
    {
        self::getLogger()->error($message, $context);
    }

    /**
     * Log a DEBUG level message.
     *
     * @param string $message
     * @param array $context
     */
    public static function debug(string $message, array $context = []): void
    {
        self::getLogger()->debug($message, $context);
    }
}
