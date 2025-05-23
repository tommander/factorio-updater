<?php

/**
 * Hello World
 */

declare(strict_types=1);

namespace TMD\FactorioUpdater\Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\WithoutErrorHandler;
use TMD\FactorioUpdater\FactorioUpdater;

/** @psalm-suppress UnusedClass */
#[CoversClass(FactorioUpdater::class)]
class FactorioUpdaterTest extends TestCase
{
	private ?FactorioUpdater $fu = null;

	protected function setUp(): void
	{
		$this->fu = new FactorioUpdater();
	}

	protected function tearDown(): void
	{
		unset($this->fu);
	}

	// public function error(string $message): void
	public function testFunctionError(): void
	{
		if (!$this->fu) {
			$this->fail('FactorioUpdater instance!');
		}

		$this->expectOutputString("[ERROR] <script>window.alert('\"Boo\"');</script>" . PHP_EOL);
		$this->fu->error('<script>window.alert(\'"Boo"\');</script>');
		$this->fu->opt_quiet = true;
		$this->fu->error('eval "$(sudo rm -rf /)"');
	}

	// public function info(string $message): void
	public function testFunctionInfo(): void
	{
		if (!$this->fu) {
			$this->fail('FactorioUpdater instance!');
		}

		$this->expectOutputString("[INFO] <script>window.location.href=\"http://hackers.org/\";</script>" . PHP_EOL);
		$this->fu->info('<script>window.location.href="http://hackers.org/";</script>');
		$this->fu->opt_quiet = true;
		$this->fu->info('sudo rm -rf /');
	}

	// public function loadOptions(): bool
	public function testFunctionLoadOptionsDefault(): void
	{
		if (!$this->fu) {
			$this->fail('FactorioUpdater instance!');
		}

		$this->fu->opt_stable = 'incorrect';
		$this->fu->opt_rootdir = '/dev/null';
		$this->fu->opt_quiet = true;
		$this->fu->opt_test = true;
		$this->fu->opt_noinstall = true;

		$this->expectOutputString('[ERROR] Missing -r / --rootdir option.' . PHP_EOL);
		$res = $this->fu->loadOptions();
		$this->assertSame(false, $res);
		$this->assertSame('stable', $this->fu->opt_stable);
		$this->assertSame('', $this->fu->opt_rootdir);
		$this->assertSame(false, $this->fu->opt_quiet);
		$this->assertSame(false, $this->fu->opt_test);
		$this->assertSame(false, $this->fu->opt_noinstall);
	}

	// public function loadOptions(): bool
	public function testFunctionLoadOptionsShortNotDir(): void
	{
		if (!$this->fu) {
			$this->fail('FactorioUpdater instance!');
		}

		$this->fu->opt_stable = '';
		$this->fu->opt_rootdir = '';
		$this->fu->opt_quiet = false;
		$this->fu->opt_test = true;
		$this->fu->opt_noinstall = false;

		$this->expectOutputString('[ERROR] Rootdir "/dev/null" not a dir.' . PHP_EOL);
		$res = $this->fu->loadOptions(['n' => false, 'r' => '/dev/null', 's' => 'experimental']);
		$this->assertSame(false, $res);
		$this->assertSame('experimental', $this->fu->opt_stable);
		$this->assertSame('/dev/null', $this->fu->opt_rootdir);
		$this->assertSame(false, $this->fu->opt_quiet);
		$this->assertSame(false, $this->fu->opt_test);
		$this->assertSame(true, $this->fu->opt_noinstall);
	}

	// public function loadOptions(): bool
	public function testFunctionLoadOptionsShortWithDir(): void
	{
		if (!$this->fu) {
			$this->fail('FactorioUpdater instance!');
		}

		$this->fu->opt_stable = '';
		$this->fu->opt_rootdir = '';
		$this->fu->opt_quiet = false;
		$this->fu->opt_test = true;
		$this->fu->opt_noinstall = false;

		$this->expectOutputString('[ERROR] Executable file "/tmp/bin/x64/factorio" does not exist.' . PHP_EOL);
		$res = $this->fu->loadOptions(['n' => false, 'r' => '/tmp/', 's' => 'experimental']);
		$this->assertSame(false, $res);
		$this->assertSame('experimental', $this->fu->opt_stable);
		$this->assertSame('/tmp/', $this->fu->opt_rootdir);
		$this->assertSame(false, $this->fu->opt_quiet);
		$this->assertSame(false, $this->fu->opt_test);
		$this->assertSame(true, $this->fu->opt_noinstall);
	}

	// public function loadOptions(): bool
	public function testFunctionLoadOptionsShortQuietFail(): void
	{
		if (!$this->fu) {
			$this->fail('FactorioUpdater instance!');
		}

		$this->fu->opt_stable = '';
		$this->fu->opt_rootdir = '';
		$this->fu->opt_quiet = false;
		$this->fu->opt_test = true;
		$this->fu->opt_noinstall = false;

		$res = $this->fu->loadOptions(['q' => false, 'n' => false, 'r' => '/tmp/', 's' => 'experimental']);
		$this->assertSame(false, $res);
		$this->assertSame('experimental', $this->fu->opt_stable);
		$this->assertSame('/tmp/', $this->fu->opt_rootdir);
		$this->assertSame(true, $this->fu->opt_quiet);
		$this->assertSame(false, $this->fu->opt_test);
		$this->assertSame(true, $this->fu->opt_noinstall);
	}

	// public function loadOptions(): bool
	public function testFunctionLoadOptionsShortTest(): void
	{
		if (!$this->fu) {
			$this->fail('FactorioUpdater instance!');
		}

		$this->fu->opt_stable = '';
		$this->fu->opt_rootdir = '';
		$this->fu->opt_quiet = false;
		$this->fu->opt_test = false;
		$this->fu->opt_noinstall = false;

		$res = $this->fu->loadOptions(['t' => false, 'n' => false, 'r' => '/dev/null/', 's' => 'experimental']);
		$this->assertSame(true, $res);
		$this->assertSame('', $this->fu->opt_stable);
		$this->assertSame('', $this->fu->opt_rootdir);
		$this->assertSame(false, $this->fu->opt_quiet);
		$this->assertSame(true, $this->fu->opt_test);
		$this->assertSame(false, $this->fu->opt_noinstall);
	}

	// public function loadOptions(): bool
	public function testFunctionLoadOptionsShortQuietTest(): void
	{
		if (!$this->fu) {
			$this->fail('FactorioUpdater instance!');
		}

		$this->fu->opt_stable = '';
		$this->fu->opt_rootdir = '';
		$this->fu->opt_quiet = false;
		$this->fu->opt_test = false;
		$this->fu->opt_noinstall = false;

		$res = $this->fu->loadOptions(['t' => false, 'q' => false, 'n' => false, 'r' => '/dev/null/', 's' => 'experimental']);
		$this->assertSame(true, $res);
		$this->assertSame('', $this->fu->opt_stable);
		$this->assertSame('', $this->fu->opt_rootdir);
		$this->assertSame(true, $this->fu->opt_quiet);
		$this->assertSame(true, $this->fu->opt_test);
		$this->assertSame(false, $this->fu->opt_noinstall);
	}

	// public function loadOptions(): bool
	public function testFunctionLoadOptionsLongNotDir(): void
	{
		if (!$this->fu) {
			$this->fail('FactorioUpdater instance!');
		}

		$this->fu->opt_stable = '';
		$this->fu->opt_rootdir = '';
		$this->fu->opt_quiet = false;
		$this->fu->opt_test = true;
		$this->fu->opt_noinstall = false;

		$this->expectOutputString('[ERROR] Rootdir "/dev/null" not a dir.' . PHP_EOL);
		$res = $this->fu->loadOptions(['no-install' => false, 'rootdir' => '/dev/null', 'stable' => 'experimental']);
		$this->assertSame(false, $res);
		$this->assertSame('experimental', $this->fu->opt_stable);
		$this->assertSame('/dev/null', $this->fu->opt_rootdir);
		$this->assertSame(false, $this->fu->opt_quiet);
		$this->assertSame(false, $this->fu->opt_test);
		$this->assertSame(true, $this->fu->opt_noinstall);
	}

	// public function loadOptions(): bool
	public function testFunctionLoadOptionsLongWithDir(): void
	{
		if (!$this->fu) {
			$this->fail('FactorioUpdater instance!');
		}

		$this->fu->opt_stable = '';
		$this->fu->opt_rootdir = '';
		$this->fu->opt_quiet = false;
		$this->fu->opt_test = true;
		$this->fu->opt_noinstall = false;

		$this->expectOutputString('[ERROR] Executable file "/tmp/bin/x64/factorio" does not exist.' . PHP_EOL);
		$res = $this->fu->loadOptions(['no-install' => false, 'rootdir' => '/tmp/', 'stable' => 'experimental']);
		$this->assertSame(false, $res);
		$this->assertSame('experimental', $this->fu->opt_stable);
		$this->assertSame('/tmp/', $this->fu->opt_rootdir);
		$this->assertSame(false, $this->fu->opt_quiet);
		$this->assertSame(false, $this->fu->opt_test);
		$this->assertSame(true, $this->fu->opt_noinstall);
	}

	// public function loadOptions(): bool
	public function testFunctionLoadOptionsLongQuietFail(): void
	{
		if (!$this->fu) {
			$this->fail('FactorioUpdater instance!');
		}

		$this->fu->opt_stable = '';
		$this->fu->opt_rootdir = '';
		$this->fu->opt_quiet = false;
		$this->fu->opt_test = true;
		$this->fu->opt_noinstall = false;

		$res = $this->fu->loadOptions(['quiet' => false, 'no-install' => false, 'rootdir' => '/tmp/', 'stable' => 'experimental']);
		$this->assertSame(false, $res);
		$this->assertSame('experimental', $this->fu->opt_stable);
		$this->assertSame('/tmp/', $this->fu->opt_rootdir);
		$this->assertSame(true, $this->fu->opt_quiet);
		$this->assertSame(false, $this->fu->opt_test);
		$this->assertSame(true, $this->fu->opt_noinstall);
	}

	// public function loadOptions(): bool
	public function testFunctionLoadOptionsLongTest(): void
	{
		if (!$this->fu) {
			$this->fail('FactorioUpdater instance!');
		}

		$this->fu->opt_stable = '';
		$this->fu->opt_rootdir = '';
		$this->fu->opt_quiet = false;
		$this->fu->opt_test = false;
		$this->fu->opt_noinstall = false;

		$res = $this->fu->loadOptions(['test' => false, 'no-install' => false, 'rootdir' => '/dev/null/', 'stable' => 'experimental']);
		$this->assertSame(true, $res);
		$this->assertSame('', $this->fu->opt_stable);
		$this->assertSame('', $this->fu->opt_rootdir);
		$this->assertSame(false, $this->fu->opt_quiet);
		$this->assertSame(true, $this->fu->opt_test);
		$this->assertSame(false, $this->fu->opt_noinstall);
	}

	// public function loadOptions(): bool
	public function testFunctionLoadOptionsLongQuietTest(): void
	{
		if (!$this->fu) {
			$this->fail('FactorioUpdater instance!');
		}

		$this->fu->opt_stable = '';
		$this->fu->opt_rootdir = '';
		$this->fu->opt_quiet = false;
		$this->fu->opt_test = false;
		$this->fu->opt_noinstall = false;

		$res = $this->fu->loadOptions(['test' => false, 'quiet' => false, 'no-install' => false, 'rootdir' => '/dev/null/', 'stable' => 'experimental']);
		$this->assertSame(true, $res);
		$this->assertSame('', $this->fu->opt_stable);
		$this->assertSame('', $this->fu->opt_rootdir);
		$this->assertSame(true, $this->fu->opt_quiet);
		$this->assertSame(true, $this->fu->opt_test);
		$this->assertSame(false, $this->fu->opt_noinstall);
	}

	// public function loadOptions(): bool
	public function testFunctionLoadOptionsShortBeforeLong(): void
	{
		if (!$this->fu) {
			$this->fail('FactorioUpdater instance!');
		}

		$this->fu->opt_stable = '';
		$this->fu->opt_rootdir = '';
		$this->fu->opt_quiet = false;
		$this->fu->opt_test = false;
		$this->fu->opt_noinstall = false;

		$this->expectOutputString('[ERROR] Rootdir "/tmp" does not end with "/".' . PHP_EOL);
		$res = $this->fu->loadOptions(['n' => false, 'no-install' => false, 'rootdir' => '/dev/null/', 'stable' => 'stable', 'r' => '/tmp', 's' => 'experimental']);
		$this->assertSame(false, $res);
		$this->assertSame('experimental', $this->fu->opt_stable);
		$this->assertSame('/tmp', $this->fu->opt_rootdir);
		$this->assertSame(false, $this->fu->opt_quiet);
		$this->assertSame(false, $this->fu->opt_test);
		$this->assertSame(true, $this->fu->opt_noinstall);
	}

	// public function loadOptions(): bool
	public function testFunctionLoadOptionsFixture(): void
	{
		if (!$this->fu) {
			$this->fail('FactorioUpdater instance!');
		}

		$this->fu->opt_stable = '';
		$this->fu->opt_rootdir = '';
		$this->fu->opt_quiet = false;
		$this->fu->opt_test = false;
		$this->fu->opt_noinstall = false;

		$res = $this->fu->loadOptions(['n' => false, 'q' => false, 'r' => __DIR__ . '/factroot/', 's' => 'experimental']);
		$this->assertSame(true, $res);
		$this->assertSame('experimental', $this->fu->opt_stable);
		$this->assertSame(__DIR__ . '/factroot/', $this->fu->opt_rootdir);
		$this->assertSame(true, $this->fu->opt_quiet);
		$this->assertSame(false, $this->fu->opt_test);
		$this->assertSame(true, $this->fu->opt_noinstall);
	}

	// public function factorioExec(?string $rootdir = null): string
	public function testFunctionFactorioExec(): void
	{
		if (!$this->fu) {
			$this->fail('FactorioUpdater instance!');
		}

		$this->fu->opt_rootdir = '/tmp/';
		$res_opt = $this->fu->factorioExec();
		$res_cust = $this->fu->factorioExec('');
		$this->assertSame('/tmp/bin/x64/factorio', $res_opt);
		$this->assertSame('bin/x64/factorio', $res_cust);
	}

	// public function validateString(mixed $something, string $regex): string|false
	public function testFunctionValidateStringNotString(): void
	{
		if (!$this->fu) {
			$this->fail('FactorioUpdater instance!');
		}

		$this->expectOutputString(
			'[ERROR] null is not a string.' . PHP_EOL .
			'[ERROR] false is not a string.' . PHP_EOL .
			'[ERROR] true is not a string.' . PHP_EOL .
			'[ERROR] 1 is not a string.' . PHP_EOL .
			'[ERROR] 1.1 is not a string.' . PHP_EOL .
			'[ERROR] {} is not a string.' . PHP_EOL .
			'[ERROR] {} is not a string.' . PHP_EOL .
			'[ERROR] [] is not a string.' . PHP_EOL .
			'[ERROR] ["a"] is not a string.' . PHP_EOL
		);
		$this->assertFalse($this->fu->validateString(null, ' '));
		$this->assertFalse($this->fu->validateString(false, ' '));
		$this->assertFalse($this->fu->validateString(true, ' '));
		$this->assertFalse($this->fu->validateString(1, ' '));
		$this->assertFalse($this->fu->validateString(1.1, ' '));
		$this->assertFalse($this->fu->validateString((new \stdClass()), ' '));
		$this->assertFalse($this->fu->validateString(static function () {
		}, ' '));
		$this->assertFalse($this->fu->validateString([], ' '));
		$this->assertFalse($this->fu->validateString(['a'], ' '));
	}

	// public function validateString(mixed $something, string $regex): string|false
	public function testFunctionValidateStringRegex(): void
	{
		if (!$this->fu) {
			$this->fail('FactorioUpdater instance!');
		}

		$this->expectOutputString(
			'[ERROR] " " is not a valid string based on "/^$/".' . PHP_EOL .
			'[ERROR] "a" is not a valid string based on "/^[A-Z]$/".' . PHP_EOL .
			'[ERROR] "A " is not a valid string based on "/^[A-Z]$/".' . PHP_EOL
		);
		$this->assertFalse($this->fu->validateString(' ', '/^$/'));
		$this->assertFalse($this->fu->validateString('a', '/^[A-Z]$/'));
		$this->assertFalse($this->fu->validateString('A ', '/^[A-Z]$/'));

		$this->assertSame('', $this->fu->validateString('', '/^$/'));
		$this->assertSame(' ', $this->fu->validateString(' ', '/^ $/'));
		$this->assertSame('AMZamz059', $this->fu->validateString('AMZamz059', '/^[A-Za-z0-9]{9}$/'));
	}

	// public function validateRootdir(): bool
	public function testFunctionValidateRootDir(): void
	{
		if (!$this->fu) {
			$this->fail('FactorioUpdater instance!');
		}

		$this->expectOutputString(
			'[ERROR] Rootdir "/doesnotexist" does not exist.' . PHP_EOL .
			'[ERROR] Rootdir "/dev/null" not a dir.' . PHP_EOL .
			'[ERROR] Rootdir "/root" not readable.' . PHP_EOL .
			'[ERROR] Rootdir "/sys" not writable.' . PHP_EOL .
			'[ERROR] Rootdir "/tmp" does not end with "/".' . PHP_EOL .
			'[ERROR] Executable file "/tmp/bin/x64/factorio" does not exist.' . PHP_EOL
		);

		$this->fu->opt_rootdir = '/doesnotexist';
		$this->assertFalse($this->fu->validateRootdir());

		$this->fu->opt_rootdir = '/dev/null';
		$this->assertFalse($this->fu->validateRootdir());

		$this->fu->opt_rootdir = '/root';
		$this->assertFalse($this->fu->validateRootdir());

		$this->fu->opt_rootdir = '/sys';
		$this->assertFalse($this->fu->validateRootdir());

		$this->fu->opt_rootdir = '/tmp';
		$this->assertFalse($this->fu->validateRootdir());

		$this->fu->opt_rootdir = '/tmp/';
		$this->assertFalse($this->fu->validateRootdir());

		//TODO: Test that <dir>/bin/x64/factorio is not executable

		$this->fu->opt_rootdir = __DIR__ . '/factroot/';
		$this->assertTrue($this->fu->validateRootdir());
	}

	// public function checkParams(): bool
	public function testFunctionCheckParams(): void
	{
		if (!$this->fu) {
			$this->fail('FactorioUpdater instance!');
		}

		$this->expectOutputString(
			'[ERROR] "" is not a valid string based on "/^[A-Za-z0-9_-]+$/".' . PHP_EOL .
			'[ERROR] Option FA_USERNAME has an invalid value "".' . PHP_EOL .
			'[ERROR] "\u0158" is not a valid string based on "/^[A-Za-z0-9_-]+$/".' . PHP_EOL .
			'[ERROR] Option FA_USERNAME has an invalid value "Ř".' . PHP_EOL .
			'[ERROR] "Abcdef789012345678901234567890" is not a valid string based on "/^[0-9a-f]{30}$/".' . PHP_EOL .
			'[ERROR] Option FA_TOKEN has an invalid value "Abcdef789012345678901234567890".' . PHP_EOL
		);

		putenv('FACTORIO_USERNAME=');
		putenv('FACTORIO_TOKEN=');
		$this->assertFalse($this->fu->checkParams());

		putenv('FACTORIO_USERNAME=Ř');
		putenv('FACTORIO_TOKEN=123456789012345678901234567890');
		$this->assertFalse($this->fu->checkParams());

		putenv('FACTORIO_USERNAME=AMZ_amz-059');
		putenv('FACTORIO_TOKEN=Abcdef789012345678901234567890');
		$this->assertFalse($this->fu->checkParams());

		putenv('FACTORIO_USERNAME=AMZ_amz-059');
		putenv('FACTORIO_TOKEN=abcdef789012345678901234567890');
		$this->assertTrue($this->fu->checkParams());
	}

	// public function downloadJson(string $url): array|false
	#[WithoutErrorHandler]
	public function testFunctionDownloadJson(): void
	{
		if (!$this->fu) {
			$this->fail('FactorioUpdater instance!');
		}

		$this->expectOutputString(
			'[ERROR] File "' . __DIR__ . '/doesnot.exist" does not exist.' . PHP_EOL .
			'[ERROR] Cannot fetch JSON from "http://www.example.com/doesnot.exist".' . PHP_EOL .
			'[ERROR] Cannot parse JSON downloaded from "/home/tommander/projects/factorio-updater/tests/assets/factoriomock_1.0.0".' . PHP_EOL .
			'<something>' . PHP_EOL .
			'Version: 1.0.0 (build 1, linux64, headless)' . PHP_EOL .
			'Version: 64' . PHP_EOL .
			'Map input version: 1.0.0-0' . PHP_EOL .
			'Map output version: 1.0.0-0' . PHP_EOL . PHP_EOL .
			'</something>' . PHP_EOL
		);

		$this->assertFalse($this->fu->downloadJson(__DIR__ . '/doesnot.exist'));
		$old_handler = set_error_handler(function (int $errno, string $errstr) {
			return ($errno === E_WARNING && $errstr === 'file_get_contents(http://www.example.com/doesnot.exist): Failed to open stream: HTTP request failed! HTTP/1.1 404 Not Found' . "\r\n");
		});
		try {
			$this->assertFalse($this->fu->downloadJson('http://www.example.com/doesnot.exist'));
		} finally {
			set_error_handler($old_handler);
		}
		$this->assertFalse($this->fu->downloadJson(__DIR__ . '/assets/factoriomock_1.0.0'));
		$this->assertSame(["/tests/assets/factoriomock_1.0.1"], $this->fu->downloadJson(__DIR__ . '/assets/get-download-link-AZaz09-123456789012345678901234567890-core-linux_headless64-1.0.0-1.0.1.json'));
	}

	// public function getLatestRelease(): string|false
	public function testFunctionGetLatestRelease(): void
	{
		if (!$this->fu) {
			$this->fail('FactorioUpdater instance!');
		}
		$this->fu->opt_test = true;

		$this->expectOutputString(
			'[ERROR] Cannot parse JSON downloaded from "/home/tommander/projects/factorio-updater/tests/testdata/latest_release_not-array.json".' . PHP_EOL .
			'<something>' . PHP_EOL .
			'true' . PHP_EOL .
			'</something>' . PHP_EOL .
			'[ERROR] false is not an array.' . PHP_EOL .
			'[ERROR] {"a":"b"} does not contain the key "stable".' . PHP_EOL .
			'[ERROR] {"stable":"yes"} key "stable" is not an array.' . PHP_EOL .
			'[ERROR] {"stable":{"maybe":"not"}} key "stable" does not contain the key "headless".' . PHP_EOL .
			'[ERROR] "a.2.3" is not a valid string based on "/^\d+\.\d+\.\d+$/".' . PHP_EOL
		);

		$this->assertFalse($this->fu->getLatestRelease(__DIR__ . '/testdata/latest_release_not-array.json'));
		$this->assertFalse($this->fu->getLatestRelease(__DIR__ . '/testdata/latest_release_miss-key.json'));
		$this->assertFalse($this->fu->getLatestRelease(__DIR__ . '/testdata/latest_release_key-not-array.json'));
		$this->assertFalse($this->fu->getLatestRelease(__DIR__ . '/testdata/latest_release_miss-build.json'));
		$this->assertFalse($this->fu->getLatestRelease(__DIR__ . '/testdata/latest_release_invalid-version.json'));
		$this->assertSame('1.1.0', $this->fu->getLatestRelease());
	}

	// public function getLocalVersion(): array|false
	public function testFunctionGetLocalVersion(): void
	{
		if (!$this->fu) {
			$this->fail('FactorioUpdater instance!');
		}

		$this->fu->opt_test = true;
		$this->expectOutputString(
			'[ERROR] The output of the program does not contain a version string.' . PHP_EOL .
			'<output>' . PHP_EOL . PHP_EOL .
			'</output>' . PHP_EOL .
			'[ERROR] Unsupported distro "linux32".' . PHP_EOL .
			'[ERROR] Unsupported build "headful".' . PHP_EOL
		);

		$this->fu->opt_rootdir = __DIR__ . '/testdata/factorio_not-version/';
		$this->assertFalse($this->fu->getLocalVersion());

		$this->fu->opt_rootdir = __DIR__ . '/testdata/factorio_bad-distro/';
		$this->assertFalse($this->fu->getLocalVersion());

		$this->fu->opt_rootdir = __DIR__ . '/testdata/factorio_bad-build/';
		$this->assertFalse($this->fu->getLocalVersion());

		$this->fu->opt_rootdir = __DIR__ . '/factroot/';
		$res = $this->fu->getLocalVersion();
		$this->assertIsArray($res);
		$this->assertArrayHasKey('version', $res);
		$this->assertArrayHasKey('buildno', $res);
		$this->assertArrayHasKey('distro', $res);
		$this->assertArrayHasKey('build', $res);
		$this->assertSame('1.0.0', $res['version']);
		$this->assertSame('1', $res['buildno']);
		$this->assertSame('linux64', $res['distro']);
		$this->assertSame('headless', $res['build']);
	}

	// public function getUpdateSequence(string $from_version, string $to_version): array|false
	public function testFunctionGetUpdateSequence(): void
	{
		if (!$this->fu) {
			$this->fail('FactorioUpdater instance!');
		}

		$this->assertTrue(true);
	}

	// public function applyUpdateSequence(array $sequence, array &$tempFiles): bool
	public function testFunctionApplyUpdateSequence(): void
	{
		if (!$this->fu) {
			$this->fail('FactorioUpdater instance!');
		}

		$this->assertTrue(true);
	}

	// public function doRun(): int
	public function testFunctionDoRun(): void
	{
		if (!$this->fu) {
			$this->fail('FactorioUpdater instance!');
		}

		$this->assertTrue(true);
	}

	// public function runtest(): int
	public function testFunctionRunTest(): void
	{
		if (!$this->fu) {
			$this->fail('FactorioUpdater instance!');
		}

		$this->assertTrue(true);
	}

	// public function run(): int
	public function testFunctionRun(): void
	{
		if (!$this->fu) {
			$this->fail('FactorioUpdater instance!');
		}

		$this->assertTrue(true);
	}
}
