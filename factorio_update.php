#!/usr/bin/php
<?php
/**
 * Factorio Update Script
 * 
 * This script checks the latest available version of Factorio and compares
 * it with the local version. If there is a new version available, it will
 * automatically attempt to update the local installation.
 * 
 * Use like this:
 * 
 * ```
 * export FAUPSC_USER='somerandomusername'
 * export FAUPSC_TOKEN='mysecrettoken'
 * ./factorio_update.php --package="core-linux_headless64" --build="headless" --stable="stable" --rootdir="/home/somebody/factorio"
 * ```
 * 
 * BEWARE! Exporting environment variables might expose the secrets in the shell history (e.g. in `~/.bash_history`)!
 * 
 * @category Nope
 * @package  Nope
 * @author   Tomáš "Tommander" Rajnoha <tomas.rajnoha@proton.me>
 * @license  MIT License https://opensource.org/license/mit
 * @version  0.1.0
 * @link     https://github.com/tommander/factorio-updater
 * @php      8.2+
 */

/**
 * Factorio package (architecture and platform).
 */
enum FactorioPackage: string {
    case CoreLinux32          = 'core-linux32';
    case CoreLinux64          = 'core-linux64';
    case CoreLinuxHeadless64  = 'core-linux_headless64';
    case CoreMac              = 'core-mac';
    case CoreMacArm64         = 'core-mac-arm64';
    case CoreMaxX64           = 'core-mac-x64';
    case CoreWin32            = 'core-win32';
    case CoreWin64            = 'core-win64';
    case CoreExpansionLinux64 = 'core_expansion-linux64';
    case CoreExpansionMac     = 'core_expansion-mac';
    case CoreExpansionWin64   = 'core_expansion-win64';
}

/**
 * Factorio build type.
 */
enum FactorioBuild: string {
    case Alpha     = 'alpha';
    case Demo      = 'demo';
    case Expansion = 'expansion';
    case Headless  = 'headless';
}

/**
 * Factorio stability branch.
 */
enum FactorioStable: string {
    case Stable       = 'stable';
    case Experimental = 'experimental';
}

/**
 * This is the class that implements the whole functionality of the script.
 */
class FactorioUpdate {
    private const URL_LATEST = 'https://factorio.com/api/latest-releases';
    private const URL_AVAILABLE = 'https://updater.factorio.com/get-available-versions';
    private const URL_UPDATE = 'https://updater.factorio.com/get-download-link?username=%1$s&token=%2$s&package=%3$s&from=%4$s&to=%5$s';
    private const URL_DOWNLOAD_PREFIX = 'https://dl.factorio.com/';
    private const FMT_VERSION = '/^\d+\.\d+\.\d+$/';
    private const FMT_USERNAME = '/^[A-Za-z0-9_-]+$/';
    private const FMT_TOKEN = '/^[0-9a-f]{30}$/';

    private FactorioPackage $opt_package = FactorioPackage::CoreLinuxHeadless64;
    private FactorioBuild $opt_build = FactorioBuild::Headless;
    private FactorioStable $opt_stable = FactorioStable::Stable;
    private string $opt_rootdir = '/';
    private string $opt_username = '';
    private string $opt_token = '';

    /**
     * Print an error message.
     * 
     * @param string $message Error message.
     * 
     * @return void
     */
    private function error(string $message): void {
        echo "[ERROR] {$message}" . PHP_EOL;
    }

    /**
     * Print an informational message.
     * 
     * @param string $message Informational message.
     * 
     * @return void
     */
    private function info(string $message): void {
        echo "[INFO] {$message}" . PHP_EOL;
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
    private function download_json(string $url): array|false {
        $str = file_get_contents($url);
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
     * Return the path to factorio executable file within the given Factorio root
     * folder.
     * 
     * @param string|null $rootdir Custom Factorio root directory or null if it should be read from script options.
     * 
     * @return string Absolute path to the Factorio executable.
     */
    private function factorio_exec(?string $rootdir = null): string {
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
    private function validate_string(mixed $something, string $regex): string|false {
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
     * @param mixed $something Absolute path to verify.
     * 
     * @return string|false
     */
    private function validate_rootdir(mixed $something): string|false {
        if (!is_string($something)) {
            $this->error("Rootdir \"{$something}\" not a string.");
            return false;
        }
        if (!is_dir($something)) {
            $this->error("Rootdir \"{$something}\" not a dir.");
            return false;
        }
        if (!file_exists($something)) {
            $this->error("Rootdir \"{$something}\" does not exist.");
            return false;
        }
        if (!is_readable($something)) {
            $this->error("Rootdir \"{$something}\" not readable.");
            return false;
        }
        if (!is_writable($something)) {
            $this->error("Rootdir \"{$something}\" not writable.");
            return false;
        }
        if (!str_ends_with($something, DIRECTORY_SEPARATOR)) {
            $this->error("Rootdir \"{$something}\" does not end with \"" . DIRECTORY_SEPARATOR . "\".");
            return false;
        }
        $factorio_exec = $this->factorio_exec($something);
        if (!file_exists($factorio_exec)) {
            $this->error("Executable file \"{$factorio_exec}\" does not exist.");
            return false;
        }
        if (!is_executable($factorio_exec)) {
            $this->error("Executable file \"{$factorio_exec}\" is not executable.");
            return false;
        }
        return $something;        
    }

    /**
     * Get the latest remote version.
     * 
     * Download the releases list from Factorio API and find the correct
     * stable/experimental build.
     * 
     * @return string|false Latest release on success, false otherwise.
     */
    private function get_latest_release(): string|false {
        $url_latest = self::URL_LATEST;
        $json_latest = $this->download_json($url_latest);
        if (!is_array($json_latest)) {
            $this->error(json_encode($json_latest) . ' is not an array.');
            return false;
        }
        if (!array_key_exists($this->opt_stable->value, $json_latest)) {
            $this->error(json_encode($json_latest) . ' does not contain the key "' . $this->opt_stable->value . '".');
            return false;
        }
        if (!is_array($json_latest[$this->opt_stable->value])) {
            $this->error(json_encode($json_latest) . ' key "' . $this->opt_stable->value . '" is not an array.');
            return false;
        }
        if (!array_key_exists($this->opt_build->value, $json_latest[$this->opt_stable->value])) {
            $this->error(json_encode($json_latest) . ' key "' . $this->opt_stable->value . '" does not contain the key "' . $this->opt_build->value . '".');
            return false;
        }

        return $this->validate_string($json_latest[$this->opt_stable->value][$this->opt_build->value], self::FMT_VERSION);
    }

    /**
     * Get the current local version.
     * 
     * Run factorio with "--version" and extract and return its version.
     * 
     * @return string|false Local version on success, false otherwise.
     */
    private function get_local_version(): string|false {
        $fx = $this->factorio_exec();
        exec("{$fx} --version", $local_output_arr);
        $local_output = trim(implode(PHP_EOL, $local_output_arr));
        if (preg_match('/^Version:\s(\d+\.\d+\.\d+)\s\(/', $local_output, $str_latest_m) !== 1) {
            $this->error('The output of the program does not contain a version string.' . PHP_EOL . '<output>' . PHP_EOL . $local_output . PHP_EOL . '</output>');
            return false;
        }

        if (count($str_latest_m) < 2) {
            $this->error('The output of the program is strange.' . PHP_EOL . '<output>' . PHP_EOL . $local_output . PHP_EOL . '</output>');
            return false;
        }

        return $this->validate_string($str_latest_m[1], self::FMT_VERSION);
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
    private function get_update_sequence(string $from_version, string $to_version): array|false {
        $url_available = self::URL_AVAILABLE;
        $json_available = $this->download_json($url_available);
        if (!is_array($json_available)) {
            $this->error(json_encode($json_available) . ' is not an array.');
            return false;
        }
        if (!array_key_exists($this->opt_package->value, $json_available)) {
            $this->error(json_encode($json_available) . ' does not contain the key "' . $this->opt_package->value . '".');
            return false;
        }
        if (!is_array($json_available[$this->opt_package->value])) {
            $this->error(json_encode($json_available) . ' key "' . $this->opt_package->value . '" is not an array.');
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
            foreach ($json_available[$this->opt_package->value] as $fromto) {
                $from = $this->validate_string($fromto['from'] ?? null, self::FMT_VERSION);
                $to = $this->validate_string($fromto['to'] ?? null, self::FMT_VERSION);
                if (!is_string($from) || !is_string($to)) {
                    continue;
                }
                if ($from !== $current_from) {
                    continue;
                }
                $empty_through = false;
                $sequence[] = ['from' => $from, 'to' => $to];
                if ($fromto['to'] === $to_version) {
                    $success = true;
                    break;
                }
                $current_from = $to;
                break;
            }
        }

        if (!$success) {
            $this->error(json_encode($json_available) . ' key "' . $this->opt_package->value . '" does not contain a sequence of updates from "' . $from_version . '" to "' . $to_version . '".');
            return false;
        }

        return $sequence;

    }

    /**
     * Load options.
     * 
     * @return bool
     */
    private function load_options(): bool {
        $opt = getopt('', ['package:', 'build:', 'stable:', 'rootdir:']);

        if (!array_key_exists('package', $opt) || !is_string($opt['package'])) {
            $this->error('Missing --package option or not a string.');
            return false;
        }
        $temp_opt_package = FactorioPackage::tryFrom($opt['package']);
        if ($temp_opt_package === null) {
            $this->error('Option --package has an incorrect value "' . $opt['package'] .'".');
            return false;
        }
        $this->opt_package = $temp_opt_package;

        if (!array_key_exists('build', $opt) || !is_string($opt['build'])) {
            $this->error('Missing --build option.');
            return false;
        }
        $temp_opt_build = FactorioBuild::tryFrom($opt['build']);
        if ($temp_opt_build === null) {
            $this->error('Option --build has an incorrect value "' . $opt['build'] .'".');
            return false;
        }
        $this->opt_build = $temp_opt_build;

        if (!array_key_exists('stable', $opt) || !is_string($opt['stable'])) {
            $this->error('Missing --stable option.');
            return false;
        }
        $temp_opt_stable = FactorioStable::tryFrom($opt['stable']);
        if ($temp_opt_stable === null) {
            $this->error('Option --stable has an incorrect value "' . $opt['stable'] .'".');
            return false;
        }
        $this->opt_stable = $temp_opt_stable;

        if (!array_key_exists('rootdir', $opt) || !is_string($opt['rootdir'])) {
            $this->error('Missing --rootdir option.');
            return false;
        }
        $temp_opt_rootdir = $this->validate_rootdir($opt['rootdir']);
        if ($temp_opt_rootdir === false) {
            $this->error('Option --rootdir contains an invalid value "' . $opt['rootdir'] . '".');
            return false;
        }
        $this->opt_rootdir = $temp_opt_rootdir;

        $temp_opt_username = $this->validate_string(getenv('FAUPSC_USER'), self::FMT_USERNAME);
        if ($temp_opt_username === false) {
            $this->error('Missing FAUPSC_USER envar or invalid value.');
            return false;
        }
        $this->opt_username = $temp_opt_username;

        $temp_opt_token = $this->validate_string(getenv('FAUPSC_TOKEN'), self::FMT_TOKEN);
        if ($temp_opt_token === false) {
            $this->error('Missing FAUPSC_TOKEN envar or invalid value.');
            return false;
        }
        $this->opt_token = $temp_opt_token;

        return true;
    }

    /**
     * Do the magic.
     * 
     * @return bool
     */
    public function run(): bool {
        // Get script parameters
        $load_result = $this->load_options();
        if ($load_result !== true) {
            $this->error('Cannot load script options.');
            return false;
        }

        // Find out latest version online
        $latest_version = $this->get_latest_release();
        if (!is_string($latest_version)) {
            $this->error('Cannot get latest release.');
            return false;
        }

        $this->info("Latest version is \"{$latest_version}\".");

        // Find out current local version
        $local_version = $this->get_local_version();
        if (!is_string($local_version)) {
            $this->error('Cannot get local version.');
            return false;
        }

        $this->info("Local version is \"{$local_version}\".");

        // Versions are the same => all good!
        if ($latest_version === $local_version) {
            $this->info('Local version is the latest one.');
            return true;
        }

        $this->info('Found a new version => update initiated.');

        // Build a sequence of updates from the "from" version to the "to" version
        // E.g. "2.0.6"=>"2.0.8" will become ['2.0.6=>2.0.7', '2.0.7=>2.0.8']
        $sequence = $this->get_update_sequence($local_version, $latest_version);
        if (!is_array($sequence)) {
            $this->error('Cannot get update sequence.');
            return false;
        }

        // One by one in the sequence: get download link, download update following that link and apply that update.
        $torem = [];
        $fx = $this->factorio_exec(); 
        $url_update = self::URL_UPDATE;
        foreach ($sequence as $one_update) {
            // Download update link
            $this->info('Downloading link for "' . $one_update['from'] . '" => "' . $one_update['to'] . '"...');
            $update_url = sprintf($url_update, $this->opt_username, $this->opt_token, $this->opt_package->value, $one_update['from'], $one_update['to']);
            $update_link_json = $this->download_json($update_url);
            if (!is_array($update_link_json) || count($update_link_json) === 0) {
                $this->error('Update link is not a non-empty array.' . PHP_EOL . json_encode($update_link_json));
                return false;
            }
            $blam = $update_link_json[0];
            if (!is_string($blam)) {
                $this->error('Update link\'s first item is not a string.' . PHP_EOL . json_encode($update_link_json));
                return false;
            }
            if (!str_starts_with($blam, self::URL_DOWNLOAD_PREFIX)) {
                $this->error('Update link\'s first item does not start with "' . self::URL_DOWNLOAD_PREFIX . '".' . PHP_EOL . json_encode($update_link_json));
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
            $torem[] = $update_file;

            // Apply update
            $this->info('Applying update...');
            exec("{$fx} --apply-update {$update_file}", $update_out, $update_res);
            if ($update_res !== 0) {
                $this->error('Update failed.' . PHP_EOL . '<output>' . PHP_EOL . implode(PHP_EOL, $update_out) . '</output>');
                return false;
            }
        }

        foreach ($torem as $onerem) {
            $this->info('Deleting temporary file "' . $onerem . '"...');
            exec('rm ' . escapeshellarg($onerem));
        }

        // Just make sure we've managed to update Factorio to the latest version.
        $new_local_version = $this->get_local_version();
        if (!is_string($new_local_version)) {
            $this->error('Cannot get new local version.');
            return false;
        }

        // All good!
        if ($new_local_version === $latest_version) {
            $this->info('All good, you have the latest version now!');
            return true;
        }

        // Umm...
        $this->error("Local version is \"{$new_local_version}\", but latest release is \"{$latest_version}\" and they are not the same => something went wrong.");
        return false;
    }
}

$faup = new FactorioUpdate();
$res = $faup->run();
if ($res !== true) {
    exit(1);
}