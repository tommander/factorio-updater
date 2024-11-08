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
 * ./fupd.php --package="core-linux_headless64" --build="headless" --stable="stable" --rootdir="/home/somebody/factorio"
 * ```
 */

declare(strict_types=1);

use Dotenv\Dotenv;
use TMD\FUPD\FactorioBuild;
use TMD\FUPD\FactorioPackage;
use TMD\FUPD\FactorioStable;
use TMD\FUPD\FactorioUpdate;
use TMD\FUPD\FactorioHelper;

require __DIR__ . '/vendor/autoload.php';

/**
 * Run test.
 *
 * @return int
 */
function runtest(): int
{
    $test_rootdir = __DIR__ . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, ['tests', 'factroot']) . DIRECTORY_SEPARATOR;
    $test_username = 'AZaz09';
    $test_token = '123456789012345678901234567890';
    foreach (FactorioPackage::cases() as $test_package) {
        $test_expectation = ($test_package === FactorioPackage::CoreLinuxHeadless64);
        foreach (FactorioBuild::cases() as $test_build) {
            foreach (FactorioStable::cases() as $test_stable) {
                FactorioHelper::info("Running test with params ({$test_package->value}, {$test_build->value}, {$test_stable->value}, {$test_rootdir}, {$test_username}, {$test_token})...");
                copy(
                    __DIR__ . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, ['tests', 'assets', 'factoriomock_1.0.0']),
                    __DIR__ . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, ['tests', 'factroot', 'bin', 'x64', 'version'])
                );
                $faup = new FactorioUpdate($test_package, $test_build, $test_stable, $test_rootdir, $test_username, $test_token, true);
                $res = $faup->run();
                if ($res !== $test_expectation) {
                    FactorioHelper::error('Test failed.');
                    return 1;
                }
            }
        }
    }
    return 0;
}

// Load options and .env variables
$opt = getopt('', ['test', 'package:', 'build:', 'stable:', 'rootdir:']);

// Option "test"
if (array_key_exists('test', $opt)) {
    exit(runtest());
}

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Option "package"
if (!array_key_exists('package', $opt) || !is_string($opt['package'])) {
    FactorioHelper::error('Missing --package option or not a string.');
    exit(1);
}
$opt_package = \TMD\FUPD\FactorioPackage::tryFrom(trim($opt['package']));
if ($opt_package === null) {
    FactorioHelper::error('Option --package has an incorrect value "' . $opt['package'] . '".');
    exit(1);
}

// Option "build"
if (!array_key_exists('build', $opt) || !is_string($opt['build'])) {
    FactorioHelper::error('Missing --build option.');
    exit(1);
}
$opt_build = \TMD\FUPD\FactorioBuild::tryFrom(trim($opt['build']));
if ($opt_build === null) {
    FactorioHelper::error('Option --build has an incorrect value "' . $opt['build'] . '".');
    exit(1);
}

// Option "stable"
if (!array_key_exists('stable', $opt) || !is_string($opt['stable'])) {
    FactorioHelper::error('Missing --stable option.');
    exit(1);
}
$opt_stable = \TMD\FUPD\FactorioStable::tryFrom(trim($opt['stable']));
if ($opt_stable === null) {
    FactorioHelper::error('Option --stable has an incorrect value "' . $opt['stable'] . '".');
    exit(1);
}

// Option "rootdir"
if (!array_key_exists('rootdir', $opt) || !is_string($opt['rootdir'])) {
    FactorioHelper::error('Missing --rootdir option.');
    exit(1);
}
$opt_rootdir = trim($opt['rootdir']);

// Environment variables
$opt_username = trim($_ENV['FA_USERNAME']);
$opt_token = trim($_ENV['FA_TOKEN']);

// Run
$faup = new TMD\FUPD\FactorioUpdate($opt_package, $opt_build, $opt_stable, $opt_rootdir, $opt_username, $opt_token, false);
$res = $faup->run();
if ($res !== true) {
    exit(1);
}
