<?php

/**
 * This file is part of the Vökuró.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Vokuro\Tests\Unit\Domain;

use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Vokuro\Domain\Meta;

final class MetaTest extends AbstractUnitTestCase
{
    /**
     * Unit Tests Vokuro\Domain\Meta :: defaults to the least privileged state
     */
    public function testFailsClosedByDefault(): void
    {
        $meta = new Meta();

        $this->assertFalse($meta->isLoggedIn);
        $this->assertSame('', $meta->name);
    }

    /**
     * Unit Tests Vokuro\Domain\Meta :: carries the signed in identity
     */
    public function testCarriesTheSignedInUser(): void
    {
        $meta = new Meta(isLoggedIn: true, name: 'Sarah Connor');

        $this->assertTrue($meta->isLoggedIn);
        $this->assertSame('Sarah Connor', $meta->name);
    }
}
