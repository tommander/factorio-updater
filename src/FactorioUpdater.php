<?php

/**
 * Factorio Updater. A PHP script for automated upgrade of headless Factorio installations.
 *
 * @copyright (c) 2024-2025 Tomáš "Tommander" Rajnoha.
 * @license MIT
 * @see https://github.com/tommander/factorio-updater
 */

declare(strict_types=1);

namespace TMD\FactorioUpdater;

/**
 * This is the only class of the Factorio Updater codebase.
 *
 * The one and only entrypoint is the method "run". All other methods, properties or constants are meant to be used only from within the class. Everything is exposed just for the purpose of unit testing.
 */
class FactorioUpdater
{
	/**
	 * Link to the list of latest Factorio releases.
	 *
	 * @var non-empty-string
	 */
	public const URL_LATEST = 'https://factorio.com/api/latest-releases';
	/**
	 * Mock of URL_LATEST for unit testing.
	 *
	 * @var non-empty-string
	 */
	public const URL_LATEST_TEST = 'string:{"experimental":{"alpha":"1.1.1","demo":"1.1.1","expansion":"1.1.1","headless":"1.1.1"},"stable":{"alpha":"1.1.0","demo":"1.1.0","expansion":"1.1.0","headless":"1.1.0"}}';
	/**
	 * Link to the list of atomic upgrades of Factorio.
	 *
	 * @var non-empty-string
	 */
	public const URL_AVAILABLE = 'https://updater.factorio.com/get-available-versions';
	/**
	 * Mock of URL_AVAILABLE for unit testing.
	 *
	 * @var non-empty-string
	 */
	public const URL_AVAILABLE_TEST = 'string:{"core-linux_headless64":[{"from":"1.0.0","to":"1.0.1"},{"from":"1.0.1","to":"1.1.0"},{"from":"1.1.0","to":"1.1.1"},{"stable":"1.1.0"}]}';
	/**
	 * Template link to the actual upgrade package link.
	 *
	 * @var non-empty-string
	 */
	public const URL_UPDATE = 'https://updater.factorio.com/get-download-link?username=%1$s&token=%2$s&package=%3$s&from=%4$s&to=%5$s';
	/**
	 * Mock of URL_UPDATE for unit testing.
	 *
	 * @var non-empty-string
	 */
	public const URL_UPDATE_TEST = "string:[\"Version: %5\$s (build 1, linux64, headless)\\nVersion: 64\\nMap input version: 1.0.0-0\\nMap output version: 1.0.0-0\"]";
	/**
	 * An upgrade package link must start with this string.
	 *
	 * @var non-empty-string
	 */
	public const URL_DOWNLOAD_PREFIX = 'https://dl.factorio.com/';
	/**
	 * Regex for verifying Factorio version strings.
	 *
	 * @var non-empty-string
	 */
	public const FMT_VERSION = '/^\d+\.\d+\.\d+$/';
	/**
	 * Regex for verifying Factorio usernames.
	 *
	 * @var non-empty-string
	 */
	public const FMT_USERNAME = '/^[A-Za-z0-9_-]+$/';
	/**
	 * Regex for verifying Factorio tokens.
	 *
	 * @var non-empty-string
	 */
	public const FMT_TOKEN = '/^[0-9a-f]{30}$/';
	/**
	 * Package name for atomic upgrades.
	 *
	 * @var non-empty-string
	 */
	public const OPT_PACKAGE = 'core-linux_headless64';
	/**
	 * Build name for Factorio.
	 *
	 * This allows the script to verify that the Factorio installation is not e.g. a normal installation which can be upgraded easily via GUI.
	 *
	 * @var non-empty-string */
	public const OPT_BUILD = 'headless';
	/**
	 * Target system for Factorio.
	 *
	 * This is a secondary way to ensure a correct Factorio installation has been chosen; other systems do not support the headless build.
	 *
	 * @var non-empty-string */
	public const OPT_DISTRO = 'linux64';
	/**
	 * A list of allowed stability flags.
	 *
	 * @var non-empty-string[] */
	public const ARR_STABLE = ['stable', 'experimental'];

	/**
	 * Current stability flag.
	 *
	 * @var string
	 */
	public string $opt_stable = 'stable';
	/**
	 * Path to the Factorio installation root directory.
	 *
	 * This is the directory that contains `bin/x64/factorio`.
	 *
	 * @var string
	 */
	public string $opt_rootdir = '';
	/**
	 * Whether echoing to STDOUT should be silenced (`true`) or not (`false`).
	 *
	 * @var bool
	 */
	public bool $opt_quiet = false;
	/**
	 * Indicates self-test.
	 *
	 * @var bool
	 */
	public bool $opt_test = false;
	/**
	 * If a new version of Factorio is available, should the script download it and install it (`false`) or not (`true`).
	 *
	 * @var bool
	 */
	public bool $opt_noinstall = false;
	/**
	 * This is used for unit testing to mimick the Factorio executable's output when called with "--version".
	 *
	 * @var string
	 */
	public string $factorio_exec_mock = '';

	/**
	 * Echo an error message to STDOUT.
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
	 * Echo an info message to STDOUT.
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
	 * Load command-line options given to the script, if any, and validate them.
	 *
	 * @param array|null $custom_opts If not null, it overrides command-line options (used in unit tests).
	 *
	 * @return bool Success or not.
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

		// Option "no-install"
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
	 * Return the path to Factorio executable file within the given Factorio root
	 * folder.
	 *
	 * @return string Absolute path to the Factorio executable.
	 */
	public function factorioExec(): string
	{
		return $this->opt_rootdir . implode(DIRECTORY_SEPARATOR, ['bin', 'x64', 'factorio']);
	}

	/**
	 * Execute Factorio with the "--version" option. In case of self-test, return the "factorio_exec_mock" property.
	 *
	 * @return string|false Standard output of the call or false in case of an error.
	 */
	public function runFactorioVersion(): string|false
	{
		if ($this->opt_test) {
			return $this->factorio_exec_mock;
		}

		$fx = $this->factorioExec();
		$res = exec("{$fx} --version", $local_output_arr);
		if ($res === false) {
			return false;
		}

		return trim(implode(PHP_EOL, $local_output_arr));
	}

	/**
	 * Execute Factorio with the "--apply-update PATH" option. In case of a self-test, write to "factorio_exec_mock" property.
	 *
	 * @param string $update_file Path to the atomic upgrade package, or the new content of "factorio_exec_mock" property for testing purposes.
	 * @param string $update_out Full content of STDOUT of the call (unused in case of a self-test).
	 *
	 * @return int Exit code of the program or 0 in case of a self-test.
	 */
	public function runFactorioApplyUpdate(string $update_file, string &$update_out): int
	{
		if ($this->opt_test) {
			$this->factorio_exec_mock = $update_file;
			return 0;
		}

		$fx = $this->factorioExec();
		exec("{$fx} --apply-update {$update_file}", $update_out_arr, $update_res);
		$update_out = trim(implode(PHP_EOL, $update_out_arr));
		return $update_res;
	}

	/**
	 * Verify that the given *something* is a string with correct format based
	 * on the given regex.
	 *
	 * @param mixed            $something This will be verified.
	 * @param non-empty-string $regex     Expected format.
	 *
	 * @return string|false The string if it's correct, false otherwise.
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
	 * Verify that the path to root directory of Factorio installation is
	 * correct.
	 *
	 * - The root directory must exist and be a directory readable+writable by the current user.
	 * - The path must end with a directory separator.
	 * - The directory must contain an executable "factorio" file on the path "bin/x64/factorio".
	 *
	 * @return bool Valid or not.
	 */
	public function validateRootdir(): bool
	{
		if ($this->opt_test) {
			return true;
		}

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
		$factorioExec = $this->factorioExec();
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
	 * Check that the environment variables FACTORIO_USERNAME and FACTORIO_TOKEN contain expected values.
	 *
	 * @return bool True if both variables are correct, false otherwise.
	 */
	public function checkParams(): bool
	{
		if ($this->opt_test) {
			return true;
		}

		$factorioUsername = getenv('FACTORIO_USERNAME');
		if ($this->validateString($factorioUsername, static::FMT_USERNAME) === false) {
			$this->error("Environment variable FACTORIO_USERNAME has an invalid value.");
			return false;
		}

		$factorioToken = getenv('FACTORIO_TOKEN');
		if ($this->validateString($factorioToken, static::FMT_TOKEN) === false) {
			$this->error("Environment variable FACTORIO_TOKEN has an invalid value.");
			return false;
		}

		return true;
	}

	/**
	 * Download the JSON from URL, parse it and return it as an associative array.
	 *
	 * Beware that the function may return null not because of failure, but because
	 * that is the content of the downloaded JSON.
	 *
	 * If the URL starts with "string:", treat the rest of the "URL" as a raw JSON. In that case, nothing needs to be downloaded and that raw JSON is parsed, checked and returned.
	 *
	 * @param string $url URL of the string (or anything acceptable to fopen).
	 *
	 * @return array|false Decoded JSON on success, false on failure.
	 */
	public function downloadJson(string $url, bool $secret = false): array|false
	{
		if (!str_starts_with($url, 'string:') && filter_var($url, FILTER_VALIDATE_URL) === false) {
			$this->error('Bad URL "' . ($secret ? '<hidden>' : $url) . '".');
			return false;
		}

		$str = str_starts_with($url, 'string:') ? substr($url, 7) : \file_get_contents($url);
		if (!is_string($str)) {
			$this->error('Cannot fetch JSON from "' . ($secret ? '<hidden>' : $url) . '".');
			return false;
		}

		/** @var mixed */
		$jsn = json_decode($str, true);
		if (!is_array($jsn) || json_last_error() !== JSON_ERROR_NONE) {
			$this->error('Cannot parse JSON downloaded from "' . ($secret ? '<hidden>' : $url) . '".' . PHP_EOL . '<something>' . PHP_EOL . $str . PHP_EOL . '</something>');
			return false;
		}

		return $jsn;
	}

	/**
	 * Get the latest remote Factorio headless version.
	 *
	 * Download the releases list from Factorio API and find the correct
	 * stable/experimental build of the headless version.
	 *
	 * @return string|false Latest release version string on success, false otherwise.
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
	 * Get the current local Factorio version.
	 *
	 * Run factorio with "--version" and extract and return its version.
	 *
	 * @return array{version: string|false, buildno: string, distro: string, build: string}|false Local version info on success, false otherwise.
	 */
	public function getLocalVersion(): array|false
	{
		$local_output = $this->runFactorioVersion();
		if ($local_output === false || preg_match('/^Version:\s(\d+\.\d+\.\d+)\s\(build\s(\d+),\s([^,]+),\s([^)]+)\)/', $local_output, $str_latest_m) !== 1 || count($str_latest_m) < 5) {
			$this->error('The output of the program does not contain a version string.' . PHP_EOL . '<output>' . PHP_EOL . (string) $local_output . PHP_EOL . '</output>');
			return false;
		}

		/** @var string[] $str_latest_m */

		if ($str_latest_m[3] !== static::OPT_DISTRO) {
			$this->error("Unsupported distro \"$str_latest_m[3]\".");
			return false;
		}

		if ($str_latest_m[4] !== static::OPT_BUILD) {
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
	 * Find a sequence of atomic upgrade packages to update from the "from" version
	 * to the "to" version.
	 *
	 * It fails if it doesn't find a path that starts exactly with "from" and ends
	 * exactly with "to" (e.g. when there is just a partial update available, e.g.
	 * "from" is "1.1.1", "to" is "1.1.3" and the available sequence is
	 * `[1.1.1->1.1.2, 1.1.2->1.1.3]`).
	 *
	 * This information is necessary to find and download the atomic upgrades as the
	 * next step.
	 *
	 * @param string $from_version From version (local).
	 * @param string $to_version   To version (remote).
	 *
	 * @return list<array{from: string,to: string}>|false Array of from-to combinations on
	 *                                              success, false otherwise.
	 */
	public function getUpdateSequence(string $from_version, string $to_version, ?string $custom_url = null): array|false
	{
		$url_available = $custom_url ?? ($this->opt_test ? static::URL_AVAILABLE_TEST : static::URL_AVAILABLE);
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
	 * Download and apply (install) each atomic upgrade package from the sequence.
	 *
	 * @param list<array{from: string, to: string}> $sequence Sequence of atomic upgrades.
	 * @param list<string> $tempFiles List of paths to temporary files created during the run of the method.
	 *
	 * @return bool True if all packages have been successfully downloaded and applied, false otherwise.
	 */
	public function applyUpdateSequence(array $sequence, array &$tempFiles, ?string $custom_url = null): bool
	{
		$url_update = $custom_url ?? ($this->opt_test ? static::URL_UPDATE_TEST : static::URL_UPDATE);
		foreach ($sequence as $one_update) {
			// Download update link
			$this->info('Downloading link for "' . $one_update['from'] . '" => "' . $one_update['to'] . '"...');
			$factorioUsername = getenv('FACTORIO_USERNAME');
			$factorioToken = getenv('FACTORIO_TOKEN');
			$update_url = sprintf($url_update, is_string($factorioUsername) ? $factorioUsername : '', is_string($factorioToken) ? $factorioToken : '', static::OPT_PACKAGE, $one_update['from'], $one_update['to']);
			$update_link_json = $this->downloadJson($update_url, true);
			if (!is_array($update_link_json) || count($update_link_json) === 0) {
				$this->error('Update link is not a non-empty array.' . PHP_EOL . json_encode($update_link_json));
				return false;
			}
			$download_link = $update_link_json[0];
			if (!is_string($download_link)) {
				$this->error('Update link\'s first item is not a string.' . PHP_EOL . json_encode($update_link_json));
				return false;
			}

			if ($this->opt_test) {
				$update_file = $download_link;
			} else {
				if (!str_starts_with($download_link, static::URL_DOWNLOAD_PREFIX)) {
					$this->error('Update link\'s first item does not start with "' . static::URL_DOWNLOAD_PREFIX . '".' . PHP_EOL . $download_link);
					return false;
				}
				$update_bin = file_get_contents($download_link);
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
			}

			// Apply update
			$this->info("Applying update \"{$update_file}\"...");
			$update_out = '';
			$update_res = $this->runFactorioApplyUpdate($update_file, $update_out);
			if ($update_res !== 0) {
				$this->error('Update failed.' . PHP_EOL . '<output>' . PHP_EOL . $update_out . '</output>');
				return false;
			}
		}

		return true;
	}

	/**
	 * Determine the local and remote Factorio version, compare them and, if requested, download and apply all atomic upgrades so that the local Factorio is up-to-date.
	 *
	 * @return int 0 on success, 1 otherwise.
	 */
	public function runReal(): int
	{
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
	 * Perform an in-memory-only self-test using all available stability flags.
	 *
	 * @return int 0 on success, 1 otherwise.
	 */
	public function runTest(): int
	{
		foreach (static::ARR_STABLE as $test_stable) {
			$this->info("Running test with param \"{$test_stable}\"...");
			$this->opt_stable = $test_stable;
			$this->opt_rootdir = '';
			$this->opt_test = true;
			$this->opt_noinstall = false;
			$this->factorio_exec_mock = "Version: 1.0.0 (build 1, linux64, headless)\nVersion: 64\nMap input version: 1.0.0-0\nMap output version: 1.0.0-0";
			$res = $this->runReal();
			if ($res !== 0) {
				$this->error('Test failed.');
				return 1;
			}
		}
		$this->info('All tests were successful.');
		return 0;
	}

	/**
	 * Run the script - load and verify command-line options, ensure that environment variables are correct and run either normal operation or self-test.
	 *
	 * This is the only method that should be called outside of its class (with the exception of unit tests).
	 *
	 * @return int 0 on success, 1 otherwise.
	 * @psalm-suppress PossiblyUnusedMethod */
	public function run(): int
	{
		if (!$this->loadOptions()) {
			$this->error('Error when loading params.');
			return 1;
		}

		if ($this->checkParams() !== true) {
			$this->error('Error when checking params.');
			return 1;
		}

		return $this->opt_test ? $this->runTest() : $this->runReal();
	}
}
