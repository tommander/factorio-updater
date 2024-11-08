<?php

/**
 * Hello.
 */

declare(strict_types=1);

namespace TMD\FUPD\Tests;

use PHPUnit\Framework\TestCase;
use TMD\FUPD\FactorioBuild;

/**
 * Woo.
 *
 * @psalm-api
 */
final class FactorioBuildTest extends TestCase
{
    /**
     * Hello.
     *
     * @return void
     */
    public function testCases(): void
    {
        $this->assertEquals(4, count(FactorioBuild::cases()), 'There should be 4 (four) build options.');
        $this->assertEquals(FactorioBuild::Alpha, FactorioBuild::tryFrom('alpha'), 'Missing "alpha" case for build options.');
        $this->assertEquals(FactorioBuild::Demo, FactorioBuild::tryFrom('demo'), 'Missing "demo" case for build options.');
        $this->assertEquals(FactorioBuild::Expansion, FactorioBuild::tryFrom('expansion'), 'Missing "expansion" case for build options.');
        $this->assertEquals(FactorioBuild::Headless, FactorioBuild::tryFrom('headless'), 'Missing "headless" case for build options.');
    }
}
