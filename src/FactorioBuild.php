<?php

/**
 * Hello World
 */

declare(strict_types=1);

namespace TMD\FUPD;

/**
 * Factorio build type.
 */
enum FactorioBuild: string
{
    case Alpha     = 'alpha';
    case Demo      = 'demo';
    case Expansion = 'expansion';
    case Headless  = 'headless';
}
