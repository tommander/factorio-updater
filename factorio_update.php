#!/usr/bin/php
<?php
/*
 * Factorio Update Script
 * 
 * This script checks the latest available version of Factorio and compares
 * it with the local version. If there is a new version available, it will
 * automatically attempt to update the local installation.
 * 
 * All other important info is in the help string.
 * 
 * @version 0.1.0
 * @author Tomáš "Tommander" Rajnoha <tomas.rajnoha@proton.me>
 * @repo https://github.com/tommander/factorio-updater
 * @license MIT
 * @php >= 8.2
 */

/**
 * Print help screen and exit.
 * 
 * @return void
 */
function print_help(): void {
    echo <<<EOS
    Factorio Update Script (Space Age Ready)
    
    Usage:
       factorio_update.php --package="" --build="" --stable="" --rootdir=""
       factorio_update.php --help
       factorio_update.php --version
    
    Options:
       --package <pkg>     Package.
       --build <bld>       Build option.
       --stable <stb>      Stable flag.
       --rootdir <pth>     Factorio root directory path (always ending with slash).
       --help              Show this screen and exits.
       --version           Show version and exits.
    
    Arguments:
       <pkg>  One of: core-linux32, core-linux64, core-linux_headless64, core-mac,
              core-mac-arm64, core-mac-x64, core-win32, core-win64,
              core_expansion-linux64, core_expansion-mac, core_expansion-win64.
       <bld>  One of: alpha, demo, expansion, headless.
       <stb>  One of: stable, experimental.
       <pth>  Valid filepath.

    Environment:
       FAUPSC_USER   Valid username (uppercase A-Z, lowercase a-z, digits 0-9).
       FAUPSC_TOKEN  Token string consisting of exactly 30 lowercase hexadecimal
                     characters (0..9, a..f).
    
    License:
       Copyright 2024 Tomáš "Tommander" Rajnoha <tomas.rajnoha@proton.me>
    
       Permission is hereby granted, free of charge, to any person obtaining a copy
       of this software and associated documentation files (the “Software”), to deal
       in the Software without restriction, including without limitation the rights
       to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
       copies of the Software, and to permit persons to whom the Software is
       furnished to do so, subject to the following conditions:
    
       The above copyright notice and this permission notice shall be included in
       all copies or substantial portions of the Software.
    
       THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
       IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
       FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
       AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
       LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
       OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
       SOFTWARE.
    EOS . PHP_EOL;
}

/**
 * Print script version and exit.
 * 
 * @return void
 */
function print_version(): void {
    echo '0.1.0' . PHP_EOL;
}

/**
 * Download the JSON from URL and return it as an associative array.
 * 
 * Beware that the function may return null not because of failure, but because
 * that is the content of the downloaded JSON.
 * 
 * @param string $url URL of the string (or anything acceptable to fopen).
 * 
 * @return mixed Decoded JSON on success, null on failure.
 */
function download_json(string $url): mixed {
$str = file_get_contents($url);
if (!is_string($str)) {
    echo '[ERROR] Cannot fetch JSON.' . PHP_EOL;
    return null;
}

    $jsn = json_decode($str, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo '[ERROR] Cannot parse JSON.' . PHP_EOL;
        return null;
    }

    return $jsn;
}

/**
 * Return the path to factorio executable file within the given Factorio root
 * folder.
 * 
 * @param string $rootdir Absolute path to root dir of  Factorio installation.
 * 
 * @return string Absolute path to the Factorio executable.
 */
function factorio_exec(string $rootdir): string {
    return $rootdir . 'bin/x64/factorio';
}

/**
 * Verify that the given *something* is a version string, i.e. three
 * non-negative numbers divided by dots. No surrounding whitespaces allowed.
 * 
 * @param mixed $something This will be verified.
 * 
 * @return bool
 */
function verify_version(mixed $something): bool {
    return (is_string($something) && (preg_match('/^\d+\.\d+\.\d+$/', $something) === 1));
}

/**
 * Verify that the given username is correct.
 * 
 * Probably there is more allowed characters, but dunno which ones.
 * 
 * @param string $username Username to verify.
 * 
 * @return bool
 */
function verify_username(string $username): bool {
    return (preg_match('/^[A-Za-z0-9_-]+$/', $username) === 1);
}

/**
 * Verify that the given token is correct.
 * 
 * Currently a token is expected to consist of 30 lowercase hexadecimal digits.
 * 
 * @param string $token Token to verify.
 * 
 * @return bool
 */
function verify_token(string $token): bool {
    return (preg_match('/^[0-9a-f]{30}$/', $token) === 1);
}

/**
 * Verify that the stability string is correct.
 * 
 * @param string $stable Stability string to verify.
 * 
 * @return bool
 */
function verify_stable(string $stable): bool {
    return in_array($stable, ['stable', 'experimental'], true);
}

/**
 * Verify that the build string is correct.
 * 
 * @param string $stable Build string to verify.
 * 
 * @return bool
 */
function verify_build(string $build): bool {
    return in_array($build, ['alpha', 'demo', 'expansion', 'headless'], true);
}

/**
 * Verify that the package string is correct.
 * 
 * @param string $stable Package string to verify.
 * 
 * @return bool
 */
function verify_package(string $package): bool {
    return in_array($package, ['core-linux32','core-linux64','core-linux_headless64','core-mac','core-mac-arm64','core-mac-x64','core-win32','core-win64','core_expansion-linux64','core_expansion-mac','core_expansion-win64'], true);
}

/**
 * Verify that the absolute path to root directory of Factorio installation is
 * correct.
 * 
 * Yeah, sure, you can use relative path, but then it's up to you to provide the
 * correct relative path to current directory.
 * 
 * @param string $rootdir Absolute path to verify.
 * 
 * @return bool
 */
function verify_rootdir(string $rootdir): bool {
    return (
        is_dir($rootdir) &&
        file_exists($rootdir) &&
        is_readable($rootdir) &&
        is_writable($rootdir) &&
        str_ends_with($rootdir, DIRECTORY_SEPARATOR) &&
        file_exists(factorio_exec($rootdir)) &&
        is_executable(factorio_exec($rootdir))
    );
}

/**
 * Get the latest remote version.
 * 
 * Download the releases list from Factorio API and find the correct
 * stable/experimental build.
 * 
 * @param string $stable Stability string.
 * @param string $build Build string.
 * 
 * @return string|false Latest release on success, false otherwise.
 */
function get_latest_release(string $stable, string $build): string|false {
    if (!verify_stable($stable) || !verify_build($build)) {
        return false;
    }

    $url_latest = 'https://factorio.com/api/latest-releases';
    $json_latest = download_json($url_latest);
    if (!is_array($json_latest) || !array_key_exists($stable, $json_latest) || !is_array($json_latest[$stable]) || !array_key_exists($build, $json_latest[$stable])) {
        return false;
    }

    $latest_version = $json_latest[$stable][$build];
    if (!verify_version($latest_version)) {
        return false;
    }

    return $latest_version;
}

/**
 * Get the current local version.
 * 
 * Run factorio with "--version" and extract and return its version.
 * 
 * @param string $rootdir Factorio root dir absolute path.
 * 
 * @return string|false Local version on success, false otherwise.
 */
function get_local_version(string $rootdir): string|false {
    if (!verify_rootdir($rootdir)) {
        return false;
    }

    $fx = factorio_exec($rootdir);
    $local_output = `{$fx} --version`;
    if (!is_string($local_output)) {
        return false;
    }

    $local_output = trim($local_output);
    if (preg_match('/^Version:\s(\d+\.\d+\.\d+)\s\(/', $local_output, $str_latest_m) !== 1) {
        return false;
    }

    if (!is_array($str_latest_m) || count($str_latest_m) < 2) {
        return false;
    }

    $local_version = $str_latest_m[1];
    if (!verify_version($local_version)) {
        return false;
    }

    return $local_version;
}

/**
 * Find a sequence of single update packages to update from the "from" version
 * to the "to" version.
 * 
 * It fails if it doesn't find a path that starts exactly with "from" and ends
 * exactly with "to" (e.g. when there is just a partial update available, e.g.
 * "from" is "1.1.1", "to" is "1.1.4" and the available sequence is 
 * `[1.1.1->1.1.2, 1.1.2->1.1.3]`).
 * 
 * @param string $package Package string.
 * @param string $from_version From version (local).
 * @param string $to_version To version (remote).
 * 
 * @return array{from: string,to: string}|false Array of from-to combinations on
 *                                              success, false otherwise.
 */
function get_update_sequence(string $package, string $from_version, string $to_version): array|false {
    if (!verify_package($package)) {
        return false;
    }

    $url_available = 'https://updater.factorio.com/get-available-versions';
    $json_available = download_json($url_available);
    if (!is_array($json_available) || !array_key_exists($package, $json_available) || !is_array($json_available[$package])) {
        return false;
    }

    $current_from = $from_version;
    $stop = false;
    $empty_through = false;
    $success = false;
    $sequence = [];
    while (!$stop && !$empty_through) {
        $empty_through = true;
        foreach ($json_available[$package] as $idx => $fromto) {
            if (!is_array($fromto) || !verify_version($fromto['from'] ?? null) || !verify_version($fromto['to'] ?? null)) {
                continue;
            }
            if ($fromto['from'] !== $current_from) {
                continue;
            }
            $empty_through = false;
            $sequence[] = ['from' => $fromto['from'], 'to' => $fromto['to']];
            if ($fromto['to'] === $to_version) {
                $success = true;
                $stop = true;
                break;
            }
            $current_from = $fromto['to'];
            break;
        }
    }

    if (!$success) {
        return false;
    }

    return $sequence;

}

////////////////////////////////////////////////////////////////////////////////
// MAIN //
//////////

// Get script parameters and if asking for help/version, print it and exit.
$opt = getopt('', ['help', 'version', 'package:', 'build:', 'stable:', 'rootdir:']);
if (array_key_exists('help', $opt)) {
    print_help();
    exit(0);
}
if (array_key_exists('version', $opt)) {
    print_version();
    exit(0);
}

// Verify parameters for auto update.
if (!array_key_exists('package', $opt) || !verify_package($opt['package'])) {
    echo '[ERROR] Missing --package option or invalid value.' . PHP_EOL;
    exit(1);
}
if (!array_key_exists('build', $opt) || !verify_build($opt['build'])) {
    echo '[ERROR] Missing --build option or invalid value.' . PHP_EOL;
    exit(1);
}
if (!array_key_exists('stable', $opt) || !verify_stable($opt['stable'])) {
    echo '[ERROR] Missing --stable option or invalid value.' . PHP_EOL;
    exit(1);
}
if (!array_key_exists('rootdir', $opt) || !verify_rootdir($opt['rootdir'])) {
    echo '[ERROR] Missing --rootdir option or invalid value.' . PHP_EOL;
    exit(1);
}
$opt_username = getenv('FAUPSC_USER');
if (!verify_username($opt_username)) {
    echo '[ERROR] Missing FAUPSC_USER envar or invalid value.' . PHP_EOL;
    exit(1);
}
$opt_token = getenv('FAUPSC_TOKEN');
if (!verify_token($opt_token)) {
    echo '[ERROR] Missing FAUPSC_TOKEN envar or invalid value.' . PHP_EOL;
    exit(1);
}

// Find out latest version online
$latest_version = get_latest_release($opt['stable'], $opt['build']);
if (!is_string($latest_version)) {
    echo '[ERROR] Cannot get latest release.' . PHP_EOL;
    exit(1);
}

printf('Latest headless stable version is "%s"' . PHP_EOL, $latest_version);

// Find out current local version
$local_version = get_local_version($opt['rootdir']);
if (!is_string($latest_version)) {
    echo '[ERROR] Cannot get local version.' . PHP_EOL;
    exit(1);
}

printf('Local version is "%s"' . PHP_EOL, $local_version);

// Versions are the same => all good!
if ($latest_version === $local_version) {
    echo '[OK] Local version is the latest one.' . PHP_EOL;
    exit(0);
}

echo '[HEADSUP] Update initiated.' . PHP_EOL;

// Build a sequence of updates from the "from" version to the "to" version
// E.g. "2.0.6"=>"2.0.8" will become ['2.0.6=>2.0.7', '2.0.7=>2.0.8']
$sequence = get_update_sequence($opt['package'], $local_version, $latest_version);
if (!is_array($sequence)) {
    echo '[ERROR] Cannot get update sequence.' . PHP_EOL;
    exit(1);
}

// One by one in the sequence: get download link, download update following that link and apply that update.
$torem = [];
$fx = factorio_exec($opt['rootdir']); 
$url_update = 'https://updater.factorio.com/get-download-link?username=%1$s&token=%2$s&package=%3$s&from=%4$s&to=%5$s';
foreach ($sequence as $one_update) {
    // Download update link
    printf('Downloading update "%s" => "%s"... ', $one_update['from'], $one_update['to']);
    $update_link_json = download_json(sprintf($url_update, $opt_username, $opt_token, $opt['package'], $one_update['from'], $one_update['to']));
    if (!is_array($update_link_json) || !is_string($update_link_json[0] ?? null) || !str_starts_with($update_link_json[0], 'https://dl.factorio.com/')) {
        echo '[ERROR] Update link has a strange structure.' . PHP_EOL;
        exit(1);
    }
    echo '[OK]' . PHP_EOL;

    // Download update
    echo 'Downloading update... ';
    $update_file = printf('%supd_%s_%s.zip', $opt['rootdir'], $one_update['from'], $one_update['to']);
    $update_bin = file_get_contents($update_link_json[0]);
    if (!is_string($update_bin)) {
        echo '' . PHP_EOL;
        exit(1);
    }
    file_put_contents($update_file, $update_bin);
    if (!file_exists($update_file)) {
        echo '[ERROR] Update file does not exist.' . PHP_EOL;
        exit(1);
    }
    echo '[OK]' . PHP_EOL;
    $torem[] = $update_file;

    // Apply update
    echo 'Applying update... ';
    exec("{$fx} --apply-update {$update_file}", $update_out, $update_res);
    if ($update_res !== 0) {
        echo '[ERROR] Update failed.' . PHP_EOL;
        echo implode(PHP_EOL, $update_out) . PHP_EOL;
        exit(1);
    }
    echo '[OK]' . PHP_EOL;
}

echo 'Removing update files... ';
foreach ($torem as $onerem) {
    exec('rm ' . escapeshellarg($onerem));
}
echo '[OK]' . PHP_EOL;

// Just make sure we've managed to update Factorio to the latest version.
$new_local_version = get_local_version($opt['rootdir']);
if (!is_string($new_local_version)) {
    echo '[ERROR] Cannot get new local version.' . PHP_EOL;
    exit(1);
}

// All good!
if ($new_local_version === $latest_version) {
    echo '[OK] Update finished successfully.' . PHP_EOL;
    exit(0);
}

// Umm...
printf('[ERROR] Local version is "%s", but latest release is "%s" and they are not the same => something went wrong...' . PHP_EOL, $new_local_version, $latest_version);
exit(1);
