<?php

/**
 * Hello.
 */

declare(strict_types=1);

namespace TMD\FUPD;

use Dotenv\Dotenv;

/**
 * This is the class that implements the whole functionality of the script.
 */
class FactorioUpdate
{
    private const URL_LATEST = 'https://factorio.com/api/latest-releases';
    private const URL_LATEST_TEST = __DIR__ . '/../tests/assets/latest-releases.json';
    private const URL_AVAILABLE = 'https://updater.factorio.com/get-available-versions';
    private const URL_AVAILABLE_TEST = __DIR__ . '/../tests/assets/get-available-versions.json';
    private const URL_UPDATE = 'https://updater.factorio.com/get-download-link?username=%1$s&token=%2$s&package=%3$s&from=%4$s&to=%5$s';
    private const URL_UPDATE_TEST = __DIR__ . '/../tests/assets/get-download-link-%1$s-%2$s-%3$s-%4$s-%5$s.json';
    private const URL_DOWNLOAD_PREFIX = 'https://dl.factorio.com/';
    private const URL_DOWNLOAD_PREFIX_TEST = __DIR__ . '/../tests/assets/factoriomock_';
    private const FMT_VERSION = '/^\d+\.\d+\.\d+$/';
    private const FMT_USERNAME = '/^[A-Za-z0-9_-]+$/';
    private const FMT_TOKEN = '/^[0-9a-f]{30}$/';
    private const OPT_PACKAGE = 'core-linux_headless64';
    private const OPT_BUILD = 'headless';

    private string $opt_stable = '';
    private string $opt_rootdir = '';
    private string $opt_username = '';
    private string $opt_token = '';
    private bool $opt_quiet = false;
    private bool $opt_test = false;
    private bool $opt_noinstall = false;

    /**
     * Error.
     *
     * @param string $message Error message.
     *
     * @return void
     */
    private function error(string $message): void
    {
        if ($this->opt_quiet) {
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
    private function info(string $message): void
    {
        if ($this->opt_quiet) {
            return;
        }
        echo '[INFO] ' . $message . PHP_EOL;
    }

    private function loadOptions(): bool
    {
        // Load options and .env variables
        $opt = getopt('', ['test', 'quiet', 'no-install', 'stable:', 'rootdir:']);

        // Options "quiet"
        $this->opt_quiet = array_key_exists('quiet', $opt);

        // Option "test"
        $this->opt_test = array_key_exists('test', $opt);
        if ($this->opt_test) {
            return true;
        }

        // Options "no-install"
        $this->opt_noinstall = array_key_exists('no-install', $opt);

        // Option "stable"
        if (!array_key_exists('stable', $opt) || !is_string($opt['stable'])) {
            !$this->opt_quiet && print('Missing --stable option.');
            return false;
        }
        $this->opt_stable = trim($opt['stable']);

        // Option "rootdir"
        if (!array_key_exists('rootdir', $opt) || !is_string($opt['rootdir'])) {
            !$this->opt_quiet && print('Missing --rootdir option.');
            return false;
        }
        $this->opt_rootdir = trim($opt['rootdir']);

        // Environment variables
        $dotenv = Dotenv::createImmutable(__DIR__);
        $dotenv->load();
        $this->opt_username = trim($_ENV['FA_USERNAME']);
        $this->opt_token = trim($_ENV['FA_TOKEN']);

        return true;
    }

    /**
     * Return the path to factorio executable file within the given Factorio root
     * folder.
     *
     * @param string|null $rootdir Custom Factorio root directory or null if it should be read from script options.
     *
     * @return string Absolute path to the Factorio executable.
     */
    private function factorioExec(?string $rootdir = null): string
    {
        return ($rootdir !== null ? $rootdir : $this->opt_rootdir) . implode(DIRECTORY_SEPARATOR, ['bin', 'x64', 'factorio']);
    }

    /**
     * Verify that the given *something* is a string with correct format based
     * on the given regex.
     *
     * @param mixed            $something This will be verified.
     * @param non-empty-string $regex     Expected format.
     *
     * @return string|false The value if it's correct, false otherwise.
     */
    private function validateString(mixed $something, string $regex): string|false
    {
        if (!is_string($something)) {
            $this->error(json_encode($something) . ' is not a string.');
            return false;
        }
        if (preg_match($regex, $something) !== 1) {
            $this->error(json_encode($something) . ' is not a valid string based on "' . $regex . '".');
            return false;
        }
        return $something;
    }

    /**
     * Verify that the absolute path to root directory of Factorio installation is
     * correct.
     *
     * Yeah, sure, you can use relative path, but then it's up to you to provide the
     * correct path relative to current directory.
     *
     * @return bool
     */
    private function validateRootdir(): bool
    {
        if (!is_dir($this->opt_rootdir)) {
            $this->error("Rootdir \"{$this->opt_rootdir}\" not a dir.");
            return false;
        }
        if (!file_exists($this->opt_rootdir)) {
            $this->error("Rootdir \"{$this->opt_rootdir}\" does not exist.");
            return false;
        }
        if (!is_readable($this->opt_rootdir)) {
            $this->error("Rootdir \"{$this->opt_rootdir}\" not readable.");
            return false;
        }
        if (!is_writable($this->opt_rootdir)) {
            $this->error("Rootdir \"{$this->opt_rootdir}\" not writable.");
            return false;
        }
        if (!str_ends_with($this->opt_rootdir, DIRECTORY_SEPARATOR)) {
            $this->error("Rootdir \"{$this->opt_rootdir}\" does not end with \"" . DIRECTORY_SEPARATOR . "\".");
            return false;
        }
        $factorioExec = $this->factorioExec($this->opt_rootdir);
        if (!file_exists($factorioExec)) {
            $this->error("Executable file \"{$factorioExec}\" does not exist.");
            return false;
        }
        if (!is_executable($factorioExec)) {
            $this->error("Executable file \"{$factorioExec}\" is not executable.");
            return false;
        }
        return true;
    }

    private function validateStable(string $something): string|false
    {
        return (in_array($something, ['stable', 'experimental'], true)) ? $something : false;
    }

    /**
     * Hello.
     *
     * @return bool
     */
    private function checkParams(): bool
    {
        $valid_stable = $this->validateStable($this->opt_stable);
        if ($valid_stable === false) {
            $this->error("Option stable has an invalid value \"{$this->opt_stable}\".");
            return false;
        }

        $valid_rootdir = $this->validateRootdir();
        if ($valid_rootdir === false) {
            $this->error("Option rootdir has an invalid value \"{$this->opt_rootdir}\".");
            return false;
        }

        $temp_opt_username = $this->validateString($this->opt_username, self::FMT_USERNAME);
        if ($temp_opt_username === false) {
            $this->error("Option FA_USERNAME has an invalid value \"{$this->opt_username}\".");
            return false;
        }

        $temp_opt_token = $this->validateString($this->opt_token, self::FMT_TOKEN);
        if ($temp_opt_token === false) {
            $this->error("Option FA_TOKEN has an invalid value \"{$this->opt_token}\".");
            return false;
        }

        return true;
    }

    /**
     * Download the JSON from URL and return it as an associative array.
     *
     * Beware that the function may return null not because of failure, but because
     * that is the content of the downloaded JSON.
     *
     * @param string $url URL of the string (or anything acceptable to fopen).
     *
     * @return array|false Decoded JSON on success, false on failure.
     */
    private function downloadJson(string $url): array|false
    {
        $str = \file_get_contents($url);
        if (!is_string($str)) {
            $this->error('Cannot fetch JSON from "' . $url . '".');
            return false;
        }

        /**
         * Mixed here is intentional.
         *
         * @psalm-suppress MixedAssignment
         */
        $jsn = json_decode($str, true);
        if (!is_array($jsn) || json_last_error() !== JSON_ERROR_NONE) {
            $this->error('Cannot parse JSON downloaded from "' . $url . '".' . PHP_EOL . '<something>' . PHP_EOL . $str . PHP_EOL . '</something>');
            return false;
        }

        return $jsn;
    }

    /**
     * Get the latest remote version.
     *
     * Download the releases list from Factorio API and find the correct
     * stable/experimental build.
     *
     * @return string|false Latest release on success, false otherwise.
     */
    private function getLatestRelease(): string|false
    {
        $url_latest = $this->opt_test ? self::URL_LATEST_TEST : self::URL_LATEST;
        $json_latest = $this->downloadJson($url_latest);
        if (!is_array($json_latest)) {
            $this->error(json_encode($json_latest) . ' is not an array.');
            return false;
        }
        if (!array_key_exists($this->opt_stable, $json_latest)) {
            $this->error(json_encode($json_latest) . ' does not contain the key "' . $this->opt_stable . '".');
            return false;
        }
        if (!is_array($json_latest[$this->opt_stable])) {
            $this->error(json_encode($json_latest) . ' key "' . $this->opt_stable . '" is not an array.');
            return false;
        }
        if (!array_key_exists((string)static::OPT_BUILD, $json_latest[$this->opt_stable])) {
            $this->error(json_encode($json_latest) . ' key "' . $this->opt_stable . '" does not contain the key "' . (string)static::OPT_BUILD . '".');
            return false;
        }

        return $this->validateString($json_latest[$this->opt_stable][(string)static::OPT_BUILD], self::FMT_VERSION);
    }

    /**
     * Get the current local version.
     *
     * Run factorio with "--version" and extract and return its version.
     *
     * @return array{version: string|false, buildno: string, distro: string, build: string}|false Local version on success, false otherwise.
     */
    private function getLocalVersion(): array|false
    {
        $fx = $this->factorioExec();
        exec("{$fx} --version", $local_output_arr);
        $local_output = trim(implode(PHP_EOL, $local_output_arr));
        if (preg_match('/^Version:\s(\d+\.\d+\.\d+)\s\(build\s(\d+),\s([^,]+),\s([^)]+)\)/', $local_output, $str_latest_m) !== 1) {
            $this->error('The output of the program does not contain a version string.' . PHP_EOL . '<output>' . PHP_EOL . $local_output . PHP_EOL . '</output>');
            return false;
        }

        if (count($str_latest_m) < 5) {
            $this->error('The output of the program is strange.' . PHP_EOL . '<output>' . PHP_EOL . $local_output . PHP_EOL . '</output>');
            return false;
        }

        if ($str_latest_m[3] !== 'linux64') {
            $this->error("Unsupported distro \"$str_latest_m[3]\".");
            return false;
        }

        if ($str_latest_m[4] !== 'headless') {
            $this->error("Unsupported build \"$str_latest_m[4]\".");
            return false;
        }

        return [
            'version' => $this->validateString($str_latest_m[1], self::FMT_VERSION),
            'buildno' => $str_latest_m[2],
            'distro' => $str_latest_m[3],
            'build' => $str_latest_m[4],
        ];
    }

    /**
     * Find a sequence of single update packages to update from the "from" version
     * to the "to" version.
     *
     * It fails if it doesn't find a path that starts exactly with "from" and ends
     * exactly with "to" (e.g. when there is just a partial update available, e.g.
     * "from" is "1.1.1", "to" is "1.1.3" and the available sequence is
     * `[1.1.1->1.1.2, 1.1.2->1.1.3]`).
     *
     * @param string $from_version From version (local).
     * @param string $to_version   To version (remote).
     *
     * @return list<array{from: string,to: string}>|false Array of from-to combinations on
     *                                              success, false otherwise.
     */
    private function getUpdateSequence(string $from_version, string $to_version): array|false
    {
        $url_available = $this->opt_test ? self::URL_AVAILABLE_TEST : self::URL_AVAILABLE;
        $json_available = $this->downloadJson($url_available);
        if (!is_array($json_available)) {
            $this->error(json_encode($json_available) . ' is not an array.');
            return false;
        }
        if (!array_key_exists((string)static::OPT_PACKAGE, $json_available)) {
            $this->error(json_encode($json_available) . ' does not contain the key "' . (string)static::OPT_PACKAGE . '".');
            return false;
        }
        if (!is_array($json_available[(string)static::OPT_PACKAGE])) {
            $this->error(json_encode($json_available) . ' key "' . (string)static::OPT_PACKAGE . '" is not an array.');
            return false;
        }

        $current_from = $from_version;
        $empty_through = false;
        $success = false;
        $sequence = [];
        while (!$empty_through) {
            $empty_through = true;
            /**
             * It's an array.
             *
             * @var array
             */
            foreach ($json_available[(string)static::OPT_PACKAGE] as $fromto) {
                $from = $this->validateString($fromto['from'] ?? null, self::FMT_VERSION);
                $to = $this->validateString($fromto['to'] ?? null, self::FMT_VERSION);
                if (!is_string($from) || !is_string($to)) {
                    continue;
                }
                if ($from !== $current_from) {
                    continue;
                }
                $empty_through = false;
                $sequence[] = ['from' => $from, 'to' => $to];
                if ($to === $to_version) {
                    $empty_through = true;
                    $success = true;
                    break;
                }
                $current_from = $to;
                break;
            }
        }

        if (!$success) {
            $this->error(json_encode($json_available) . ' key "' . (string)static::OPT_PACKAGE . '" does not contain a sequence of updates from "' . $from_version . '" to "' . $to_version . '".');
            return false;
        }

        return $sequence;
    }

    /**
     * Hello.
     *
     * @param list<array{from: string, to: string}> $sequence Sequence.
     * @param list<string> $tempFiles Temp files.
     *
     * @return bool
     */
    private function applyUpdateSequence(array $sequence, array &$tempFiles): bool
    {
        $fx = $this->factorioExec();
        $url_update = $this->opt_test ? self::URL_UPDATE_TEST : self::URL_UPDATE;
        foreach ($sequence as $one_update) {
            // Download update link
            $this->info('Downloading link for "' . $one_update['from'] . '" => "' . $one_update['to'] . '"...');
            $update_url = sprintf($url_update, $this->opt_username, $this->opt_token, (string)static::OPT_PACKAGE, $one_update['from'], $one_update['to']);
            $update_link_json = $this->downloadJson($update_url);
            if (!is_array($update_link_json) || count($update_link_json) === 0) {
                $this->error('Update link is not a non-empty array.' . PHP_EOL . json_encode($update_link_json));
                return false;
            }
            $blam = $update_link_json[0];
            if (!is_string($blam)) {
                $this->error('Update link\'s first item is not a string.' . PHP_EOL . json_encode($update_link_json));
                return false;
            }
            if ($this->opt_test) {
                $blam = __DIR__ . DIRECTORY_SEPARATOR . '..' . $blam;
            }
            $url_download_prefix = $this->opt_test ? self::URL_DOWNLOAD_PREFIX_TEST : self::URL_DOWNLOAD_PREFIX;
            if (!str_starts_with($blam, $url_download_prefix)) {
                $this->error('Update link\'s first item does not start with "' . $url_download_prefix . '".' . PHP_EOL . $blam);
                return false;
            }
            $update_bin = file_get_contents($blam);
            if (!is_string($update_bin)) {
                $this->error('The downloaded update binary is not a string.' . PHP_EOL . json_encode($update_bin));
                return false;
            }

            // Save update binary
            $this->info('Saving the downloaded update binary...');
            $update_file = sprintf('%supd_%s_%s.zip', $this->opt_rootdir, $one_update['from'], $one_update['to']);
            file_put_contents($update_file, $update_bin);
            if (!file_exists($update_file)) {
                $this->error('Update file "' . $update_file . '" does not exist.');
                return false;
            }
            $tempFiles[] = $update_file;

            // Apply update
            $this->info('Applying update...' . "{$fx} --apply-update {$update_file}");
            exec("{$fx} --apply-update {$update_file}", $update_out, $update_res);
            if ($update_res !== 0) {
                $this->error('Update failed.' . PHP_EOL . '<output>' . PHP_EOL . implode(PHP_EOL, $update_out) . '</output>');
                return false;
            }
        }

        return true;
    }

    /**
     * Do the magic.
     *
     * @return bool
     */
    public function run(): bool
    {
        $resLoad = $this->loadOptions();
        if (!$resLoad) {
            $this->error('Error when loading params.');
            return false;
        }

        if ($this->opt_test) {
            return ($this->runtest() === 0);
        }

        return $this->doRun();
    }

    /**
     * Do the magic.
     *
     * @return bool
     */
    private function doRun(): bool
    {
        if ($this->checkParams() !== true) {
            $this->error('Error when checking params.');
            return false;
        }

        // Find out current local version
        $local_version = $this->getLocalVersion();
        if (!is_array($local_version)) {
            $this->error('Cannot get local version.');
            return false;
        }
        if (!is_string($local_version['version'])) {
            $this->error('Cannot get local version string.');
            return false;
        }

        $this->info("Local version is \"{$local_version['version']}\".");

        // Find out latest version online
        $latest_version = $this->getLatestRelease();
        if (!is_string($latest_version)) {
            $this->error('Cannot get latest release.');
            return false;
        }

        $this->info("Latest version is \"{$latest_version}\".");

        // Versions are the same => all good!
        if ($latest_version === $local_version['version']) {
            $this->info('Local version is the latest one.');
            return true;
        }

        if ($this->opt_noinstall) {
            $this->info('Found a new version, but no-install requested => all done.');
            return true;
        }

        $this->info('Found a new version => update initiated.');

        // Build a sequence of updates from the "from" version to the "to" version
        // E.g. "2.0.6"=>"2.0.8" will become ['2.0.6=>2.0.7', '2.0.7=>2.0.8']
        $sequence = $this->getUpdateSequence($local_version['version'], $latest_version);
        if (!is_array($sequence)) {
            $this->error('Cannot get update sequence.');
            return false;
        }


        // One by one in the sequence: get download link, download update following that link and apply that update.
        $torem = [];
        $applyResult = $this->applyUpdateSequence($sequence, $torem);
        if (!$applyResult) {
            $this->error('Cannot apply update sequence.');
            return false;
        }

        foreach ($torem as $onerem) {
            $this->info('Deleting temporary file "' . $onerem . '"...');
            if (!str_starts_with($onerem, $this->opt_rootdir)) {
                $this->info("Cannot delete file \"{$onerem}\" because it appears to be outside of Factorio rootdir.");
                continue;
            }
            exec('rm ' . escapeshellarg($onerem));
        }

        // Just make sure we've managed to update Factorio to the latest version.
        $new_local_version = $this->getLocalVersion();
        if (!is_array($new_local_version)) {
            $this->error('Cannot get new local version.');
            return false;
        }
        if (!is_string($new_local_version['version'])) {
            $this->error('Cannot get new local version string.');
            return false;
        }

        // All good!
        if ($new_local_version['version'] === $latest_version) {
            $this->info('All good, you have the latest version now!');
            return true;
        }

        // Umm...
        $this->error("Local version is \"{$new_local_version['version']}\", but latest release is \"{$latest_version}\" and they are not the same => something went wrong.");
        return false;
    }

    /**
     * Run test.
     *
     * @return int
     */
    private function runtest(): int
    {
        $init_version = function (): void {
            copy(
                __DIR__ . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, ['..', 'tests', 'assets', 'factoriomock_1.0.0']),
                __DIR__ . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, ['..', 'tests', 'factroot', 'bin', 'x64', 'version'])
            );
        };
        $test_rootdir = __DIR__ . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, ['..', 'tests', 'factroot']) . DIRECTORY_SEPARATOR;
        $test_username = 'AZaz09';
        $test_token = '123456789012345678901234567890';
        foreach (['stable', 'experimental'] as $test_stable) {
            $this->info("Running test with params ({$test_stable}, {$test_rootdir}, {$test_username}, {$test_token})...");
            $init_version();
            $this->opt_stable = $test_stable;
            $this->opt_rootdir = $test_rootdir;
            $this->opt_username = $test_username;
            $this->opt_token = $test_token;
            $this->opt_test = true;
            $this->opt_noinstall = false;
            $res = $this->doRun();
            if ($res !== true) {
                $this->error('Test failed.');
                return 1;
            }
        }
        $init_version();
        $this->info('All tests were successful.');
        return 0;
    }
}
