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
 * ./FactorioUpdater.php --stable="stable" --rootdir="/home/somebody/factorio"
 * ```
 */

declare(strict_types=1);

namespace TMD\FactorioUpdater;

class FactorioUpdater
{
	/** @var non-empty-string */
	public const URL_LATEST = 'https://factorio.com/api/latest-releases';
	/** @var non-empty-string */
	public const URL_LATEST_TEST = __DIR__ . '/tests/assets/latest-releases.json';
	/** @var non-empty-string */
	public const URL_AVAILABLE = 'https://updater.factorio.com/get-available-versions';
	/** @var non-empty-string */
	public const URL_AVAILABLE_TEST = __DIR__ . '/tests/assets/get-available-versions.json';
	/** @var non-empty-string */
	public const URL_UPDATE = 'https://updater.factorio.com/get-download-link?username=%1$s&token=%2$s&package=%3$s&from=%4$s&to=%5$s';
	/** @var non-empty-string */
	public const URL_UPDATE_TEST = __DIR__ . '/tests/assets/get-download-link-%1$s-%2$s-%3$s-%4$s-%5$s.json';
	/** @var non-empty-string */
	public const URL_DOWNLOAD_PREFIX = 'https://dl.factorio.com/';
	/** @var non-empty-string */
	public const URL_DOWNLOAD_PREFIX_TEST = __DIR__ . '/tests/assets/factoriomock_';
	/** @var non-empty-string */
	public const FMT_VERSION = '/^\d+\.\d+\.\d+$/';
	/** @var non-empty-string */
	public const FMT_USERNAME = '/^[A-Za-z0-9_-]+$/';
	/** @var non-empty-string */
	public const FMT_TOKEN = '/^[0-9a-f]{30}$/';
	/** @var non-empty-string */
	public const OPT_PACKAGE = 'core-linux_headless64';
	/** @var non-empty-string */
	public const OPT_BUILD = 'headless';
	/** @var non-empty-string[] */
	public const ARR_STABLE = ['stable', 'experimental'];

	public string $opt_stable = 'stable';
	public string $opt_rootdir = '';
	public bool $opt_quiet = false;
	public bool $opt_test = false;
	public bool $opt_noinstall = false;
	public string $opt_username = 'AZaz09';
	public string $opt_token = '123456789012345678901234567890';

	/**
	 * Error.
	 *
	 * @param string $message Error message.
	 *
	 * @return void
	 */
	public function error(string $message): void
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
	public function info(string $message): void
	{
		if ($this->opt_quiet) {
			return;
		}
		echo '[INFO] ' . $message . PHP_EOL;
	}

	/**
	 * Load options given to the script, if any.
	 */
	public function loadOptions(?array $custom_opts = null): bool
	{
		// Load options and .env variables
		$opt = $custom_opts ?? getopt('tqns:r:', ['test', 'quiet', 'no-install', 'stable:', 'rootdir:']);

		if (!is_array($opt)) {
			return false;
		}

		// Options "quiet"
		$this->opt_quiet = (array_key_exists('q', $opt) || array_key_exists('quiet', $opt));

		// Option "test"
		$this->opt_test = (array_key_exists('t', $opt) || array_key_exists('test', $opt));
		if ($this->opt_test) {
			return true;
		}

		// Options "no-install"
		$this->opt_noinstall = (array_key_exists('n', $opt) || array_key_exists('no-install', $opt));

		// Option "stable"
		$this->opt_stable = 'stable';
		if (array_key_exists('s', $opt) && is_string($opt['s']) && in_array($opt['s'], static::ARR_STABLE, true)) {
			$this->opt_stable = $opt['s'];
		} elseif (array_key_exists('stable', $opt) && is_string($opt['stable']) && in_array($opt['stable'], static::ARR_STABLE, true)) {
			$this->opt_stable = $opt['stable'];
		}

		// Option "rootdir"
		$this->opt_rootdir = '';
		if (array_key_exists('r', $opt) && is_string($opt['r']) && !empty($opt['r'])) {
			$this->opt_rootdir = $opt['r'];
		} elseif (array_key_exists('rootdir', $opt) && is_string($opt['rootdir']) && !empty($opt['rootdir'])) {
			$this->opt_rootdir = $opt['rootdir'];
		}
		if (empty($this->opt_rootdir)) {
			!$this->opt_quiet && $this->error('Missing -r / --rootdir option.');
			return false;
		}
		if (!$this->validateRootdir()) {
			return false;
		}

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
	public function factorioExec(?string $rootdir = null): string
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
	public function validateString(mixed $something, string $regex): string|false
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
	public function validateRootdir(): bool
	{
		if (!file_exists($this->opt_rootdir)) {
			$this->error("Rootdir \"{$this->opt_rootdir}\" does not exist.");
			return false;
		}
		if (!is_dir($this->opt_rootdir)) {
			$this->error("Rootdir \"{$this->opt_rootdir}\" not a dir.");
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

	/**
	 * Hello.
	 *
	 * @return bool
	 */
	public function checkParams(): bool
	{
		$this->opt_username = (string) getenv('FACTORIO_USERNAME', true);
		$this->opt_token = (string) getenv('FACTORIO_TOKEN', true);

		if ($this->validateString($this->opt_username, static::FMT_USERNAME) === false) {
			$this->error('Option FA_USERNAME has an invalid value "' . $this->opt_username . '".');
			return false;
		}

		if ($this->validateString($this->opt_token, static::FMT_TOKEN) === false) {
			$this->error('Option FA_TOKEN has an invalid value "' . $this->opt_token . '".');
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
	public function downloadJson(string $url): array|false
	{
		$parsed_url = filter_var($url, FILTER_VALIDATE_URL);
		if ($parsed_url === false && !file_exists($url)) {
			$this->error('File "' . $url . '" does not exist.');
			return false;
		}

		$str = \file_get_contents($url);
		if (!is_string($str)) {
			$this->error('Cannot fetch JSON from "' . $url . '".');
			return false;
		}

		/** @var mixed */
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
	public function getLatestRelease(?string $custom_url = null): string|false
	{
		$url_latest = $custom_url ?? ($this->opt_test ? static::URL_LATEST_TEST : static::URL_LATEST);
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
		if (!array_key_exists(static::OPT_BUILD, $json_latest[$this->opt_stable])) {
			$this->error(json_encode($json_latest) . ' key "' . $this->opt_stable . '" does not contain the key "' . static::OPT_BUILD . '".');
			return false;
		}

		return $this->validateString($json_latest[$this->opt_stable][static::OPT_BUILD], static::FMT_VERSION);
	}

	/**
	 * Get the current local version.
	 *
	 * Run factorio with "--version" and extract and return its version.
	 *
	 * @return array{version: string|false, buildno: string, distro: string, build: string}|false Local version on success, false otherwise.
	 */
	public function getLocalVersion(): array|false
	{
		$fx = $this->factorioExec();
		exec("{$fx} --version", $local_output_arr);
		$local_output = trim(implode(PHP_EOL, $local_output_arr));
		if (preg_match('/^Version:\s(\d+\.\d+\.\d+)\s\(build\s(\d+),\s([^,]+),\s([^)]+)\)/', $local_output, $str_latest_m) !== 1 || count($str_latest_m) < 5) {
			$this->error('The output of the program does not contain a version string.' . PHP_EOL . '<output>' . PHP_EOL . $local_output . PHP_EOL . '</output>');
			return false;
		}

		/** @var string[] $str_latest_m */

		if ($str_latest_m[3] !== 'linux64') {
			$this->error("Unsupported distro \"$str_latest_m[3]\".");
			return false;
		}

		if ($str_latest_m[4] !== 'headless') {
			$this->error("Unsupported build \"$str_latest_m[4]\".");
			return false;
		}

		return [
			'version' => $this->validateString($str_latest_m[1], static::FMT_VERSION),
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
	public function getUpdateSequence(string $from_version, string $to_version): array|false
	{
		$url_available = $this->opt_test ? static::URL_AVAILABLE_TEST : static::URL_AVAILABLE;
		$json_available = $this->downloadJson($url_available);
		if (!is_array($json_available)) {
			$this->error(json_encode($json_available) . ' is not an array.');
			return false;
		}
		if (!array_key_exists(static::OPT_PACKAGE, $json_available)) {
			$this->error(json_encode($json_available) . ' does not contain the key "' . static::OPT_PACKAGE . '".');
			return false;
		}
		if (!is_array($json_available[static::OPT_PACKAGE])) {
			$this->error(json_encode($json_available) . ' key "' . static::OPT_PACKAGE . '" is not an array.');
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
			foreach ($json_available[static::OPT_PACKAGE] as $fromto) {
				$from = $this->validateString($fromto['from'] ?? null, static::FMT_VERSION);
				$to = $this->validateString($fromto['to'] ?? null, static::FMT_VERSION);
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
			$this->error(json_encode($json_available) . ' key "' . static::OPT_PACKAGE . '" does not contain a sequence of updates from "' . $from_version . '" to "' . $to_version . '".');
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
	public function applyUpdateSequence(array $sequence, array &$tempFiles): bool
	{
		$fx = $this->factorioExec();
		$url_update = $this->opt_test ? static::URL_UPDATE_TEST : static::URL_UPDATE;
		foreach ($sequence as $one_update) {
			// Download update link
			$this->info('Downloading link for "' . $one_update['from'] . '" => "' . $one_update['to'] . '"...');
			$update_url = sprintf($url_update, $this->opt_username, $this->opt_token, static::OPT_PACKAGE, $one_update['from'], $one_update['to']);
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
				$blam = __DIR__ . $blam;
			}
			$url_download_prefix = $this->opt_test ? static::URL_DOWNLOAD_PREFIX_TEST : static::URL_DOWNLOAD_PREFIX;
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
			$update_file = sprintf('%1$supd_%2$s_%3$s.zip', $this->opt_rootdir, $one_update['from'], $one_update['to']);
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
	 * @return int
	 */
	public function doRun(): int
	{
		if ($this->checkParams() !== true) {
			$this->error('Error when checking params.');
			return 1;
		}

		// Find out current local version
		$local_version = $this->getLocalVersion();
		if (!is_array($local_version)) {
			$this->error('Cannot get local version.');
			return 1;
		}
		if (!is_string($local_version['version'])) {
			$this->error('Cannot get local version string.');
			return 1;
		}

		$this->info("Local version is \"{$local_version['version']}\".");

		// Find out latest version online
		$latest_version = $this->getLatestRelease();
		if (!is_string($latest_version)) {
			$this->error('Cannot get latest release.');
			return 1;
		}

		$this->info("Latest version is \"{$latest_version}\".");

		// Versions are the same => all good!
		if ($latest_version === $local_version['version']) {
			$this->info('Local version is the latest one.');
			return 0;
		}

		if ($this->opt_noinstall) {
			$this->info('Found a new version, but no-install requested => all done.');
			return 0;
		}

		$this->info('Found a new version => update initiated.');

		// Build a sequence of updates from the "from" version to the "to" version
		// E.g. "2.0.6"=>"2.0.8" will become ['2.0.6=>2.0.7', '2.0.7=>2.0.8']
		$sequence = $this->getUpdateSequence($local_version['version'], $latest_version);
		if (!is_array($sequence)) {
			$this->error('Cannot get update sequence.');
			return 1;
		}


		// One by one in the sequence: get download link, download update following that link and apply that update.
		$torem = [];
		$applyResult = $this->applyUpdateSequence($sequence, $torem);
		if (!$applyResult) {
			$this->error('Cannot apply update sequence.');
			return 1;
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
			return 1;
		}
		if (!is_string($new_local_version['version'])) {
			$this->error('Cannot get new local version string.');
			return 1;
		}

		// All good!
		if ($new_local_version['version'] === $latest_version) {
			$this->info('All good, you have the latest version now!');
			return 0;
		}

		// Umm...
		$this->error("Local version is \"{$new_local_version['version']}\", but latest release is \"{$latest_version}\" and they are not the same => something went wrong.");
		return 1;
	}

	/**
	 * Run test.
	 *
	 * @return int
	 */
	public function runtest(): int
	{
		if (!file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'tests')) {
			$this->error('Tests cannot run without the tests directory.');
			return 1;
		}

		$init_version = function (): void {
			copy(
				implode(DIRECTORY_SEPARATOR, [__DIR__, 'tests', 'assets', 'factoriomock_1.0.0']),
				implode(DIRECTORY_SEPARATOR, [__DIR__, 'tests', 'factroot', 'bin', 'x64', 'version'])
			);
		};
		$test_rootdir = implode(DIRECTORY_SEPARATOR, [__DIR__, 'tests', 'factroot']) . DIRECTORY_SEPARATOR;
		foreach (static::ARR_STABLE as $test_stable) {
			$this->info("Running test with params ({$test_stable}, {$test_rootdir})...");
			$init_version();
			$this->opt_stable = $test_stable;
			$this->opt_rootdir = $test_rootdir;
			$this->opt_test = true;
			$this->opt_noinstall = false;
			$this->opt_username = 'AZaz09';
			$this->opt_token = '123456789012345678901234567890';
			$res = $this->doRun();
			if ($res !== 0) {
				$this->error('Test failed.');
				return 1;
			}
		}
		$init_version();
		$this->info('All tests were successful.');
		return 0;
	}

	public function run(): int
	{
		if (!$this->loadOptions()) {
			$this->error('Error when loading params.');
			return 1;
		}

		return $this->opt_test ? $this->runtest() : $this->doRun();
	}

	public static function scriptIncluded(): bool
	{
		return (isset($_SERVER['SCRIPT_FILENAME']) && realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'])) || !defined('PHPUNIT_COMPOSER_INSTALL');
	}
}

if (FactorioUpdater::scriptIncluded()) {
	exit((new FactorioUpdater())->run());
}
