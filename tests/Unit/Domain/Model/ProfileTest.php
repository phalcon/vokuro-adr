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

namespace Vokuro\Tests\Unit\Domain\Model;

use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Vokuro\Domain\Model\Profile;

final class ProfileTest extends AbstractUnitTestCase
{
    /**
     * Unit Tests Vokuro\Domain\Model\Profile :: holds its fields
     */
    public function testHoldsItsFields(): void
    {
        $profile = new Profile(id: 3, name: 'Administrators', active: true);

        $this->assertSame(3, $profile->id);
        $this->assertSame('Administrators', $profile->name);
        $this->assertTrue($profile->active);
    }
}
