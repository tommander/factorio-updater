<?php

/**
 * Hello World.
 */

declare(strict_types=1);

namespace TMD\FUPD;

/**
 * Factorio package (architecture and platform).
 */
enum FactorioPackage: string
{
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
