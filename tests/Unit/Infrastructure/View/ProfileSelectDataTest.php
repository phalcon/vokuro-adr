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

namespace Vokuro\Tests\Unit\Infrastructure\View;

use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Vokuro\Infrastructure\View\ProfileSelectData;

final class ProfileSelectDataTest extends AbstractUnitTestCase
{
    /**
     * Unit Tests Vokuro\Infrastructure\View\ProfileSelectData :: exposes the profiles as options
     */
    public function testExposesTheProfilesAsOptions(): void
    {
        $data = new ProfileSelectData([1 => 'Admins', 2 => 'Users']);

        $this->assertSame([1 => 'Admins', 2 => 'Users'], $data->getOptions());
        $this->assertSame([], $data->getAttributes());
    }
}
