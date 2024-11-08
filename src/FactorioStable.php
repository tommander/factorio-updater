<?php

/**
 * Hello World
 */

declare(strict_types=1);

namespace TMD\FUPD;

/**
 * Factorio stability branch.
 */
enum FactorioStable: string
{
    case Stable       = 'stable';
    case Experimental = 'experimental';
}
