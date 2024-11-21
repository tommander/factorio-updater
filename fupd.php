#!/usr/bin/php
<?php

/**
 * Factorio Updater
 *
 * This script checks the latest available version of Factorio and compares
 * it with the local version. If there is a new version available, it will
 * automatically attempt to update the local installation.
 *
 * Use like this:
 *
 * ```
 * ./fupd.php --stable="stable" --rootdir="/home/somebody/factorio"
 * ```
 */

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

exit((new TMD\FUPD\FactorioUpdate())->run() ? 0 : 1);
