<?php

/**
 * Hello.
 */

declare(strict_types=1);

namespace TMD\FUPD\Tests;

use PHPUnit\Framework\TestCase;
use TMD\FUPD\FactorioPackage;

/**
 * Woo.
 *
 * @psalm-api
 */
final class FactorioPackageTest extends TestCase
{
    /**
     * Hello.
     *
     * @return void
     */
    public function testCases(): void
    {
        $this->assertEquals(11, count(FactorioPackage::cases()), 'There should be 11 (eleven) package options.');
        $this->assertEquals(FactorioPackage::CoreLinux32, FactorioPackage::tryFrom('core-linux32'), 'Missing "core-linux32" case for package options.');
        $this->assertEquals(FactorioPackage::CoreLinux64, FactorioPackage::tryFrom('core-linux64'), 'Missing "core-linux64" case for package options');
        $this->assertEquals(FactorioPackage::CoreLinuxHeadless64, FactorioPackage::tryFrom('core-linux_headless64'), 'Missing "core-linux_headless64" case for package options');
        $this->assertEquals(FactorioPackage::CoreMac, FactorioPackage::tryFrom('core-mac'), 'Missing "core-mac" case for package options');
        $this->assertEquals(FactorioPackage::CoreMacArm64, FactorioPackage::tryFrom('core-mac-arm64'), 'Missing "mac-arm64" case for package options');
        $this->assertEquals(FactorioPackage::CoreMaxX64, FactorioPackage::tryFrom('core-mac-x64'), 'Missing "mac-x64" case for package options');
        $this->assertEquals(FactorioPackage::CoreWin32, FactorioPackage::tryFrom('core-win32'), 'Missing "core-win32" case for package options');
        $this->assertEquals(FactorioPackage::CoreWin64, FactorioPackage::tryFrom('core-win64'), 'Missing "core-win64" case for package options');
        $this->assertEquals(FactorioPackage::CoreExpansionLinux64, FactorioPackage::tryFrom('core_expansion-linux64'), 'Missing "core_expansion-linux64" case for package options');
        $this->assertEquals(FactorioPackage::CoreExpansionMac, FactorioPackage::tryFrom('core_expansion-mac'), 'Missing "core_expansion-mac" case for package options');
        $this->assertEquals(FactorioPackage::CoreExpansionWin64, FactorioPackage::tryFrom('core_expansion-win64'), 'Missing "core_expansion-win64" case for package options');
    }
}
