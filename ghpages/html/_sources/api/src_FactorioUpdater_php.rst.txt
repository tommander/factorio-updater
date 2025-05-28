Class FactorioUpdater
=====================
Factorio Updater. A PHP script for automated upgrade of headless Factorio installations.

:tag: Unknown
:tag: Unknown
:tag: Unknown
:package: Application

.. php:namespace :: TMD\FactorioUpdater

.. php:class :: FactorioUpdater

   This is the only class of the Factorio Updater codebase.

   The one and only entrypoint is the method "run". All other methods, properties or constants are meant to be used only from within the class. Everything is exposed just for the purpose of unit testing.

   :package: Application

.. php:const :: URL_LATEST : 'https://factorio.com/api/latest-releases'

   Link to the list of latest Factorio releases.

   :var non-empty-string: 

.. php:const :: URL_LATEST_TEST : 'string:{"experimental":{"alpha":"1.1.1","demo":"1.1.1","expansion":"1.1.1","headless":"1.1.1"},"stable":{"alpha":"1.1.0","demo":"1.1.0","expansion":"1.1.0","headless":"1.1.0"}}'

   Mock of URL_LATEST for unit testing.

   :var non-empty-string: 

.. php:const :: URL_AVAILABLE : 'https://updater.factorio.com/get-available-versions'

   Link to the list of atomic upgrades of Factorio.

   :var non-empty-string: 

.. php:const :: URL_AVAILABLE_TEST : 'string:{"core-linux_headless64":[{"from":"1.0.0","to":"1.0.1"},{"from":"1.0.1","to":"1.1.0"},{"from":"1.1.0","to":"1.1.1"},{"stable":"1.1.0"}]}'

   Mock of URL_AVAILABLE for unit testing.

   :var non-empty-string: 

.. php:const :: URL_UPDATE : 'https://updater.factorio.com/get-download-link?username=%1$s&token=%2$s&package=%3$s&from=%4$s&to=%5$s'

   Template link to the actual upgrade package link.

   :var non-empty-string: 

.. php:const :: URL_UPDATE_TEST : "string:[\"Version: %5\$s (build 1, linux64, headless)\\nVersion: 64\\nMap input version: 1.0.0-0\\nMap output version: 1.0.0-0\"]"

   Mock of URL_UPDATE for unit testing.

   :var non-empty-string: 

.. php:const :: URL_DOWNLOAD_PREFIX : 'https://dl.factorio.com/'

   An upgrade package link must start with this string.

   :var non-empty-string: 

.. php:const :: FMT_VERSION : '/^\d+\.\d+\.\d+$/'

   Regex for verifying Factorio version strings.

   :var non-empty-string: 

.. php:const :: FMT_USERNAME : '/^[A-Za-z0-9_-]+$/'

   Regex for verifying Factorio usernames.

   :var non-empty-string: 

.. php:const :: FMT_TOKEN : '/^[0-9a-f]{30}$/'

   Regex for verifying Factorio tokens.

   :var non-empty-string: 

.. php:const :: OPT_PACKAGE : 'core-linux_headless64'

   Package name for atomic upgrades.

   :var non-empty-string: 

.. php:const :: OPT_BUILD : 'headless'

   Build name for Factorio.

   This allows the script to verify that the Factorio installation is not e.g. a normal installation which can be upgraded easily via GUI.

   :var non-empty-string: 

.. php:const :: OPT_DISTRO : 'linux64'

   Target system for Factorio.

   This is a secondary way to ensure a correct Factorio installation has been chosen; other systems do not support the headless build.

   :var non-empty-string: 

.. php:const :: ARR_STABLE : ['stable', 'experimental']

   A list of allowed stability flags.

   :var non-empty-string[]: 

.. php:attr :: opt_stable : 'stable'

   Current stability flag.

   :var string: 

.. php:attr :: opt_rootdir : ''

   Path to the Factorio installation root directory.

   This is the directory that contains `bin/x64/factorio`.

   :var string: 

.. php:attr :: opt_quiet : false

   Whether echoing to STDOUT should be silenced (`true`) or not (`false`).

   :var bool: 

.. php:attr :: opt_test : false

   Indicates self-test.

   :var bool: 

.. php:attr :: opt_noinstall : false

   If a new version of Factorio is available, should the script download it and install it (`false`) or not (`true`).

   :var bool: 

.. php:attr :: factorio_exec_mock : ''

   This is used for unit testing to mimick the Factorio executable's output when called with "--version".

   :var string: 

.. php:method :: error() 

   Echo an error message to STDOUT.

   :param string message: Error message.
   :return: 
   :returntype: void

.. php:method :: info() 

   Echo an info message to STDOUT.

   :param string message: Info message.
   :return: 
   :returntype: void

.. php:method :: loadOptions() 

   Load command-line options given to the script, if any, and validate them.

   :param array|null custom_opts: If not null, it overrides command-line options (used in unit tests).
   :return: Success or not.
   :returntype: bool

.. php:method :: factorioExec() 

   Return the path to Factorio executable file within the given Factorio root
   folder.

   :return: Absolute path to the Factorio executable.
   :returntype: string

.. php:method :: runFactorioVersion() 

   Execute Factorio with the "--version" option. In case of self-test, return the "factorio_exec_mock" property.

   :return: Standard output of the call or false in case of an error.
   :returntype: string|false

.. php:method :: runFactorioApplyUpdate() 

   Execute Factorio with the "--apply-update PATH" option. In case of a self-test, write to "factorio_exec_mock" property.

   :param string update_file: Path to the atomic upgrade package, or the new content of "factorio_exec_mock" property for testing purposes.
   :param string update_out: Full content of STDOUT of the call (unused in case of a self-test).
   :return: Exit code of the program or 0 in case of a self-test.
   :returntype: int

.. php:method :: validateString() 

   Verify that the given *something* is a string with correct format based
   on the given regex.

   :param mixed something: This will be verified.
   :param non-empty-string regex: Expected format.
   :return: The string if it's correct, false otherwise.
   :returntype: string|false

.. php:method :: validateRootdir() 

   Verify that the path to root directory of Factorio installation is
   correct.

   - The root directory must exist and be a directory readable+writable by the current user.
   - The path must end with a directory separator.
   - The directory must contain an executable "factorio" file on the path "bin/x64/factorio".

   :return: Valid or not.
   :returntype: bool

.. php:method :: checkParams() 

   Check that the environment variables FACTORIO_USERNAME and FACTORIO_TOKEN contain expected values.

   :return: True if both variables are correct, false otherwise.
   :returntype: bool

.. php:method :: downloadJson() 

   Download the JSON from URL, parse it and return it as an associative array.

   Beware that the function may return null not because of failure, but because
   that is the content of the downloaded JSON.

   If the URL starts with "string:", treat the rest of the "URL" as a raw JSON. In that case, nothing needs to be downloaded and that raw JSON is parsed, checked and returned.

   :param string url: URL of the string (or anything acceptable to fopen).
   :return: Decoded JSON on success, false on failure.
   :returntype: array|false

.. php:method :: getLatestRelease() 

   Get the latest remote Factorio headless version.

   Download the releases list from Factorio API and find the correct
   stable/experimental build of the headless version.

   :return: Latest release version string on success, false otherwise.
   :returntype: string|false

.. php:method :: getLocalVersion() 

   Get the current local Factorio version.

   Run factorio with "--version" and extract and return its version.

   :return: Local version info on success, false otherwise.
   :returntype: array{version: string|false, buildno: string, distro: string, build: string}|false

.. php:method :: getUpdateSequence() 

   Find a sequence of atomic upgrade packages to update from the "from" version
   to the "to" version.

   It fails if it doesn't find a path that starts exactly with "from" and ends
   exactly with "to" (e.g. when there is just a partial update available, e.g.
   "from" is "1.1.1", "to" is "1.1.3" and the available sequence is
   `[1.1.1->1.1.2, 1.1.2->1.1.3]`).

   This information is necessary to find and download the atomic upgrades as the
   next step.

   :param string from_version: From version (local).
   :param string to_version: To version (remote).
   :return: Array of from-to combinations on
   success, false otherwise.
   :returntype: list<array{from: string, to: string}>|false

.. php:method :: applyUpdateSequence() 

   Download and apply (install) each atomic upgrade package from the sequence.

   :param list<array{from: string, to: string}> sequence: Sequence of atomic upgrades.
   :param list<string> tempFiles: List of paths to temporary files created during the run of the method.
   :return: True if all packages have been successfully downloaded and applied, false otherwise.
   :returntype: bool

.. php:method :: runReal() 

   Determine the local and remote Factorio version, compare them and, if requested, download and apply all atomic upgrades so that the local Factorio is up-to-date.

   :return: 0 on success, 1 otherwise.
   :returntype: int

.. php:method :: runTest() 

   Perform an in-memory-only self-test using all available stability flags.

   :return: 0 on success, 1 otherwise.
   :returntype: int

.. php:method :: run() 

   Run the script - load and verify command-line options, ensure that environment variables are correct and run either normal operation or self-test.

   This is the only method that should be called outside of its class (with the exception of unit tests).

   :return: 0 on success, 1 otherwise.
   :returntype: int
   :psalm-suppress: PossiblyUnusedMethod
