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
use Vokuro\Domain\Model\User;

final class UserTest extends AbstractUnitTestCase
{
    /**
     * Unit Tests Vokuro\Domain\Model\User :: holds its fields
     */
    public function testHoldsItsFields(): void
    {
        $user = new User(
            id: 7,
            name: 'Sarah Connor',
            email: 'sarah@skynet.dev',
            passwordHash: '$2y$hash',
            profileId: 2,
            profileName: 'Users',
            active: true,
            banned: false,
            suspended: false,
            mustChangePassword: true
        );

        $this->assertSame(7, $user->id);
        $this->assertSame('Sarah Connor', $user->name);
        $this->assertSame('sarah@skynet.dev', $user->email);
        $this->assertSame('$2y$hash', $user->passwordHash);
        $this->assertSame(2, $user->profileId);
        $this->assertSame('Users', $user->profileName);
        $this->assertTrue($user->active);
        $this->assertFalse($user->banned);
        $this->assertFalse($user->suspended);
        $this->assertTrue($user->mustChangePassword);
    }
}
