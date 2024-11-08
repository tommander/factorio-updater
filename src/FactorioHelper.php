<?php

/**
 * Hello World.
 */

declare(strict_types=1);

namespace TMD\FUPD;

class FactorioHelper
{
    /**
     * Error.
     *
     * @param string $message Error message.
     *
     * @return void
     */
    public static function error(string $message): void
    {
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
        echo '[INFO] ' . $message . PHP_EOL;
    }
}
