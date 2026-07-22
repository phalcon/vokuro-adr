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

namespace Vokuro\Tests\Unit\Domain\Collection;

use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Vokuro\Domain\Collection\PasswordChangeCollection;
use Vokuro\Domain\Collection\ProfileCollection;
use Vokuro\Domain\Collection\ResetPasswordCollection;
use Vokuro\Domain\Collection\SuccessLoginCollection;
use Vokuro\Domain\Collection\UserCollection;
use Vokuro\Domain\Model\PasswordChange;
use Vokuro\Domain\Model\Profile;
use Vokuro\Domain\Model\ResetPassword;
use Vokuro\Domain\Model\SuccessLogin;
use Vokuro\Domain\Model\User;

final class CollectionsTest extends AbstractUnitTestCase
{
    /**
     * Unit Tests Vokuro\Domain\Collection\UserCollection :: keys by id and iterates
     */
    public function testUserCollectionKeysById(): void
    {
        $one = $this->user(1);
        $two = $this->user(2);

        $collection = new UserCollection([$one, $two]);

        $this->assertCount(2, $collection);
        $this->assertFalse($collection->isEmpty());
        $this->assertSame($one, $collection->get('1'));
        $this->assertSame($two, $collection->get('2'));

        $seen = [];
        foreach ($collection as $user) {
            $seen[] = $user->id;
        }

        $this->assertSame([1, 2], $seen);
    }

    /**
     * Unit Tests Vokuro\Domain\Collection\UserCollection :: is empty when built empty
     */
    public function testEmptyCollection(): void
    {
        $this->assertTrue((new UserCollection())->isEmpty());
    }

    /**
     * Unit Tests the profile and audit collections hold their records
     */
    public function testTypedCollections(): void
    {
        $profiles = new ProfileCollection([new Profile(3, 'Admins', true)]);
        $logins   = new SuccessLoginCollection([new SuccessLogin(4, 'ip', 'ua')]);
        $changes  = new PasswordChangeCollection([new PasswordChange(5, 'ip', 'ua', 1)]);
        $resets   = new ResetPasswordCollection([new ResetPassword(6, 1, false)]);

        $this->assertCount(1, $profiles);
        $this->assertInstanceOf(Profile::class, $profiles->get('3'));
        $this->assertCount(1, $logins);
        $this->assertInstanceOf(SuccessLogin::class, $logins->get('4'));
        $this->assertCount(1, $changes);
        $this->assertInstanceOf(PasswordChange::class, $changes->get('5'));
        $this->assertCount(1, $resets);
        $this->assertInstanceOf(ResetPassword::class, $resets->get('6'));
    }

    private function user(int $id): User
    {
        return new User(
            id: $id,
            name: 'User ' . $id,
            email: $id . '@x.dev',
            passwordHash: 'h',
            profileId: 1,
            profileName: 'Users',
            active: true,
            banned: false,
            suspended: false,
            mustChangePassword: false
        );
    }
}
