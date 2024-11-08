<?php

/**
 * Hello.
 */

declare(strict_types=1);

namespace TMD\FUPD\Tests;

use PHPUnit\Framework\TestCase;
use TMD\FUPD\FactorioStable;

/**
 * Woo.
 *
 * @psalm-api
 */
final class FactorioStableTest extends TestCase
{
    /**
     * Hello.
     *
     * @return void
     */
    public function testCases(): void
    {
        $this->assertEquals(2, count(FactorioStable::cases()), 'There should be 2 (two) stable options.');
        $this->assertEquals(FactorioStable::Stable, FactorioStable::tryFrom('stable'), 'Missing "stable" case for stable options.');
        $this->assertEquals(FactorioStable::Experimental, FactorioStable::tryFrom('experimental'), 'Missing "experimental" case for stable options.');
    }
}
