<?php

/**
 * Hello World
 */

declare(strict_types=1);

namespace TMD\FactorioUpdater\Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
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
		(!$this->fu) && $this->fail('FactorioUpdater instance!');

		$this->expectOutputString("[ERROR] <script>window.alert('\"Boo\"');</script>" . PHP_EOL);
		$this->fu->error('<script>window.alert(\'"Boo"\');</script>');
		$this->fu->opt_quiet = true;
		$this->fu->error('eval "$(sudo rm -rf /)"');
	}

	// public function info(string $message): void
	public function testFunctionInfo(): void
	{
		(!$this->fu) && $this->fail('FactorioUpdater instance!');

		$this->expectOutputString("[INFO] <script>window.location.href=\"http://hackers.org/\";</script>" . PHP_EOL);
		$this->fu->info('<script>window.location.href="http://hackers.org/";</script>');
		$this->fu->opt_quiet = true;
		$this->fu->info('sudo rm -rf /');
	}

	// public function loadOptions(): bool
	public function testFunctionLoadOptionsDefault(): void
	{
		(!$this->fu) && $this->fail('FactorioUpdater instance!');

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
		(!$this->fu) && $this->fail('FactorioUpdater instance!');

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
		(!$this->fu) && $this->fail('FactorioUpdater instance!');

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
		(!$this->fu) && $this->fail('FactorioUpdater instance!');

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
		(!$this->fu) && $this->fail('FactorioUpdater instance!');

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
		(!$this->fu) && $this->fail('FactorioUpdater instance!');

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
		(!$this->fu) && $this->fail('FactorioUpdater instance!');

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
		(!$this->fu) && $this->fail('FactorioUpdater instance!');

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
		(!$this->fu) && $this->fail('FactorioUpdater instance!');

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
		(!$this->fu) && $this->fail('FactorioUpdater instance!');

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
		(!$this->fu) && $this->fail('FactorioUpdater instance!');

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
		(!$this->fu) && $this->fail('FactorioUpdater instance!');

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
		(!$this->fu) && $this->fail('FactorioUpdater instance!');

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
		(!$this->fu) && $this->fail('FactorioUpdater instance!');

		$this->fu->opt_rootdir = '/tmp/';
		$res_opt = $this->fu->factorioExec();
		$this->assertSame('/tmp/bin/x64/factorio', $res_opt);
	}

	// public function runFactorioVersion(): string|false
	public function testFunctionRunFactorioVersion(): void
	{
		(!$this->fu) && $this->fail('FactorioUpdater instance!');

		$this->fu->opt_test = true;
		$this->fu->factorio_exec_mock = 'Abcd1234';
		$this->assertSame($this->fu->factorio_exec_mock, $this->fu->runFactorioVersion());
	}

	// public function runFactorioApplyUpdate(string $update_file, string &$update_out): int
	public function testFunctionRunFactorioApplyUpdate(): void
	{
		(!$this->fu) && $this->fail('FactorioUpdater instance!');

		$this->fu->opt_test = true;
		$out = '';
		$res = $this->fu->runFactorioApplyUpdate('Xyz987', $out);
		$this->assertSame('Xyz987', $this->fu->factorio_exec_mock);
		$this->assertSame('', $out);
		$this->assertSame(0, $res);
	}

	// public function validateString(mixed $something, string $regex): string|false
	public function testFunctionValidateStringNotString(): void
	{
		(!$this->fu) && $this->fail('FactorioUpdater instance!');

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
		$this->assertSame('', $this->fu->validateString('', '/.*/'));
		$this->assertSame(\stdClass::class, $this->fu->validateString(\stdClass::class, '/.*/'));
	}

	// public function validateString(mixed $something, string $regex): string|false
	public function testFunctionValidateStringRegex(): void
	{
		(!$this->fu) && $this->fail('FactorioUpdater instance!');

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
		(!$this->fu) && $this->fail('FactorioUpdater instance!');

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
		(!$this->fu) && $this->fail('FactorioUpdater instance!');

		$this->fu->opt_test = true;
		$this->assertTrue($this->fu->checkParams());
	}

	// public function downloadJson(string $url): array|false
	public function testFunctionDownloadJson(): void
	{
		(!$this->fu) && $this->fail('FactorioUpdater instance!');

		$this->expectOutputString(
			'[ERROR] Cannot parse JSON downloaded from "string:".' . PHP_EOL .
			'<something>' . PHP_EOL . PHP_EOL .
			'</something>' . PHP_EOL .
			'[ERROR] Cannot parse JSON downloaded from "string:null".' . PHP_EOL .
			'<something>' . PHP_EOL .
			'null' . PHP_EOL .
			'</something>' . PHP_EOL
		);

		$this->assertFalse($this->fu->downloadJson('string:'));
		$this->assertFalse($this->fu->downloadJson('string:null'));
		$this->assertSame([], $this->fu->downloadJson('string:{}'));
		$this->assertSame([], $this->fu->downloadJson('string:[]'));
		$this->assertSame(['a'], $this->fu->downloadJson('string:["a"]'));
		$this->assertSame(['a' => 1], $this->fu->downloadJson('string:{"a":1}'));
	}

	// public function getLatestRelease(): string|false
	public function testFunctionGetLatestRelease(): void
	{
		(!$this->fu) && $this->fail('FactorioUpdater instance!');
		$this->fu->opt_test = true;

		$this->expectOutputString(
			'[ERROR] Cannot parse JSON downloaded from "string:true".' . PHP_EOL .
			'<something>' . PHP_EOL .
			'true' . PHP_EOL .
			'</something>' . PHP_EOL .
			'[ERROR] false is not an array.' . PHP_EOL .
			'[ERROR] {"a":"b"} does not contain the key "stable".' . PHP_EOL .
			'[ERROR] {"stable":"yes"} key "stable" is not an array.' . PHP_EOL .
			'[ERROR] {"stable":{"maybe":"not"}} key "stable" does not contain the key "headless".' . PHP_EOL .
			'[ERROR] "a.2.3" is not a valid string based on "/^\d+\.\d+\.\d+$/".' . PHP_EOL
		);

		$this->assertFalse($this->fu->getLatestRelease('string:true'));
		$this->assertFalse($this->fu->getLatestRelease('string:{"a": "b"}'));
		$this->assertFalse($this->fu->getLatestRelease('string:{"stable": "yes"}'));
		$this->assertFalse($this->fu->getLatestRelease('string:{"stable": {"maybe": "not"}}'));
		$this->assertFalse($this->fu->getLatestRelease('string:{"stable": {"headless": "a.2.3"}}'));
		$this->assertSame('1.1.0', $this->fu->getLatestRelease());
	}

	// public function getLocalVersion(): array|false
	public function testFunctionGetLocalVersion(): void
	{
		(!$this->fu) && $this->fail('FactorioUpdater instance!');

		$this->fu->opt_test = true;
		$this->expectOutputString(
			'[ERROR] The output of the program does not contain a version string.' . PHP_EOL .
			'<output>' . PHP_EOL .
			'BOO!' . PHP_EOL . PHP_EOL .
			'</output>' . PHP_EOL .
			'[ERROR] Unsupported distro "linux32".' . PHP_EOL .
			'[ERROR] Unsupported build "headful".' . PHP_EOL
		);

		$this->fu->factorio_exec_mock = "BOO!\n";
		$this->assertFalse($this->fu->getLocalVersion());

		$this->fu->factorio_exec_mock = "Version: 1.0.0 (build 1, linux32, headful)\nVersion: 64\nMap input version: 1.0.0-0\nMap output version: 1.0.0-0\n";
		$this->assertFalse($this->fu->getLocalVersion());

		$this->fu->factorio_exec_mock = "Version: 1.0.0 (build 1, linux64, headful)\nVersion: 64\nMap input version: 1.0.0-0\nMap output version: 1.0.0-0\n";
		$this->assertFalse($this->fu->getLocalVersion());

		$this->fu->factorio_exec_mock = "Version: 1.0.0 (build 1, linux64, headless)\nVersion: 64\nMap input version: 1.0.0-0\nMap output version: 1.0.0-0\n";
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
		(!$this->fu) && $this->fail('FactorioUpdater instance!');
		$this->fu->opt_test = true;

		$this->expectOutputString(
			'[ERROR] Cannot parse JSON downloaded from "string:true".' . PHP_EOL .
			'<something>' . PHP_EOL .
			'true' . PHP_EOL .
			'</something>' . PHP_EOL .
			'[ERROR] false is not an array.' . PHP_EOL .
			'[ERROR] {"a":"b"} does not contain the key "core-linux_headless64".' . PHP_EOL .
			'[ERROR] {"core-linux_headless64":"yes"} key "core-linux_headless64" is not an array.' . PHP_EOL .
			'[ERROR] null is not a string.' . PHP_EOL .
			'[ERROR] null is not a string.' . PHP_EOL .
			'[ERROR] {"core-linux_headless64":[{"from":"1.0.0","to":"1.0.1"},{"from":"1.0.1","to":"1.1.0"},{"from":"1.1.0","to":"1.1.1"},{"stable":"1.1.0"}]} key "core-linux_headless64" does not contain a sequence of updates from "1.0.0" to "1.2.0".' . PHP_EOL .
			'[ERROR] null is not a string.' . PHP_EOL .
			'[ERROR] null is not a string.' . PHP_EOL .
			'[ERROR] {"core-linux_headless64":[{"from":"1.0.0","to":"1.0.1"},{"from":"1.0.1","to":"1.1.0"},{"from":"1.1.0","to":"1.1.1"},{"stable":"1.1.0"}]} key "core-linux_headless64" does not contain a sequence of updates from "0.1.0" to "1.1.1".' . PHP_EOL
		);
		$this->assertFalse($this->fu->getUpdateSequence('', '', 'string:true'));
		$this->assertFalse($this->fu->getUpdateSequence('', '', 'string:{"a": "b"}'));
		$this->assertFalse($this->fu->getUpdateSequence('', '', 'string:{"core-linux_headless64": "yes"}'));
		$this->assertFalse($this->fu->getUpdateSequence('1.0.0', '1.2.0'));
		$this->assertFalse($this->fu->getUpdateSequence('0.1.0', '1.1.1'));
		$this->assertSame([['from' => '1.0.0', 'to' => '1.0.1']], $this->fu->getUpdateSequence('1.0.0', '1.0.1'));
		$this->assertSame([['from' => '1.0.0', 'to' => '1.0.1'], ['from' => '1.0.1', 'to' => '1.1.0'], ['from' => '1.1.0', 'to' => '1.1.1']], $this->fu->getUpdateSequence('1.0.0', '1.1.1'));
	}

	// public function applyUpdateSequence(array $sequence, array &$tempFiles): bool
	public function testFunctionApplyUpdateSequence(): void
	{
		(!$this->fu) && $this->fail('FactorioUpdater instance!');
		$this->fu->opt_test = true;
		$tempfiles = [];

		$this->expectOutputString(
			'[INFO] Downloading link for "1.0.0" => "1.0.1"...' . PHP_EOL .
			'[ERROR] Cannot parse JSON downloaded from "<hidden>".' . PHP_EOL .
			'<something>' . PHP_EOL . PHP_EOL .
			'</something>' . PHP_EOL .
			'[ERROR] Update link is not a non-empty array.' . PHP_EOL .
			'false' . PHP_EOL .

			'[INFO] Downloading link for "1.0.0" => "1.0.1"...' . PHP_EOL .
			'[ERROR] Cannot parse JSON downloaded from "<hidden>".' . PHP_EOL .
			'<something>' . PHP_EOL .
			'false' . PHP_EOL .
			'</something>' . PHP_EOL .
			'[ERROR] Update link is not a non-empty array.' . PHP_EOL .
			'false' . PHP_EOL .

			'[INFO] Downloading link for "1.0.0" => "1.0.1"...' . PHP_EOL .
			'[ERROR] Update link is not a non-empty array.' . PHP_EOL .
			'[]' . PHP_EOL .

			'[INFO] Downloading link for "1.0.0" => "1.0.1"...' . PHP_EOL .
			'[ERROR] Update link\'s first item is not a string.' . PHP_EOL .
			'[false]' . PHP_EOL .

			'[INFO] Downloading link for "1.0.0" => "1.0.1"...' . PHP_EOL .
			'[INFO] Applying update "Version: 1.0.1 (build 1, linux64, headless)' . PHP_EOL .
			'Version: 64' . PHP_EOL .
			'Map input version: 1.0.0-0' . PHP_EOL .
			'Map output version: 1.0.0-0"...' . PHP_EOL
		);

		$this->assertTrue($this->fu->applyUpdateSequence([], $tempfiles, 'string:'));
		$this->assertFalse($this->fu->applyUpdateSequence([['from' => '1.0.0', 'to' => '1.0.1']], $tempfiles, 'string:'));
		$this->assertFalse($this->fu->applyUpdateSequence([['from' => '1.0.0', 'to' => '1.0.1']], $tempfiles, 'string:false'));
		$this->assertFalse($this->fu->applyUpdateSequence([['from' => '1.0.0', 'to' => '1.0.1']], $tempfiles, 'string:[]'));
		$this->assertFalse($this->fu->applyUpdateSequence([['from' => '1.0.0', 'to' => '1.0.1']], $tempfiles, 'string:[false]'));
		$this->assertTrue($this->fu->applyUpdateSequence([['from' => '1.0.0', 'to' => '1.0.1']], $tempfiles));
		$this->assertSame([], $tempfiles);
	}
}
