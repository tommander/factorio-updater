<?php

/**
 * Hello World.
 */

declare(strict_types=1);

namespace TMD\FUPD;

class FactorioHelper
{
    /**
     * Use this to prevent Factorio Updater from printing out anything.
     */
    public static bool $quiet = false;

    /**
     * Error.
     *
     * @param string $message Error message.
     *
     * @return void
     */
    public static function error(string $message): void
    {
        if (self::$quiet) {
            return;
        }
        echo '[ERROR] ' . $message . PHP_EOL;
    }

    /**
     * Info.
     *
     * @param string $message Info message.
     *
     * @return void
     */
    public static function info(string $message): void
    {
        if (self::$quiet) {
            return;
        }
        echo '[INFO] ' . $message . PHP_EOL;
    }
}
