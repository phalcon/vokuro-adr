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

namespace Vokuro\Tests\Unit\Application;

use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Vokuro\Application\RememberMe;
use Vokuro\Contracts\Cookies;
use Vokuro\Contracts\Repository\RememberTokenRepository;
use Vokuro\Contracts\Repository\UserRepository;
use Vokuro\Domain\Model\User;

final class RememberMeTest extends AbstractUnitTestCase
{
    /**
     * Unit Tests Vokuro\Application\RememberMe :: stores a hashed token and sets the cookies
     */
    public function testRememberStoresHashedTokenAndCookies(): void
    {
        $tokens = $this->createMock(RememberTokenRepository::class);
        $tokens->expects($this->once())->method('add')
               ->with(7, $this->isType('string'), 'agent');

        $cookies = $this->createMock(Cookies::class);
        $cookies->expects($this->exactly(2))->method('set');

        (new RememberMe($tokens, $this->createMock(UserRepository::class), $cookies))
            ->remember(7, 'agent');
    }

    /**
     * Unit Tests Vokuro\Application\RememberMe :: recalls a valid cookie into an identity
     */
    public function testRecallReturnsIdentity(): void
    {
        $raw = 'raw-token';

        $tokens = $this->createMock(RememberTokenRepository::class);
        $tokens->method('findUserByToken')->with(hash('sha256', $raw))->willReturn(7);

        $users = $this->createMock(UserRepository::class);
        $users->method('findById')->with(7)->willReturn($this->user());

        $auth = (new RememberMe($tokens, $users, $this->cookies((string) 7, $raw)))->recall();

        $this->assertSame(
            ['id' => 7, 'name' => 'Sarah', 'email' => 's@x.dev', 'profilesId' => 2],
            $auth
        );
    }

    /**
     * Unit Tests Vokuro\Application\RememberMe :: recall rejects missing, tampered and mismatched cookies
     */
    public function testRecallRejectsBadCookies(): void
    {
        $users = $this->createMock(UserRepository::class);

        // no cookies at all
        $none = new RememberMe($this->createMock(RememberTokenRepository::class), $users, $this->cookies(null, null));
        $this->assertNull($none->recall());

        // token unknown to the store
        $tokens = $this->createMock(RememberTokenRepository::class);
        $tokens->method('findUserByToken')->willReturn(null);
        $unknown = new RememberMe($tokens, $users, $this->cookies('7', 'raw'));
        $this->assertNull($unknown->recall());

        // token belongs to a different user than the RMU cookie
        $other = $this->createMock(RememberTokenRepository::class);
        $other->method('findUserByToken')->willReturn(9);
        $mismatch = new RememberMe($other, $users, $this->cookies('7', 'raw'));
        $this->assertNull($mismatch->recall());

        // token maps to a user who no longer exists
        $orphan = $this->createMock(RememberTokenRepository::class);
        $orphan->method('findUserByToken')->willReturn(7);
        $users->method('findById')->willReturn(null);
        $gone = new RememberMe($orphan, $users, $this->cookies('7', 'raw'));
        $this->assertNull($gone->recall());
    }

    /**
     * Unit Tests Vokuro\Application\RememberMe :: forget drops the token and the cookies
     */
    public function testForget(): void
    {
        $tokens = $this->createMock(RememberTokenRepository::class);
        $tokens->expects($this->once())->method('deleteForUser')->with(7);

        $cookies = $this->createMock(Cookies::class);
        $cookies->expects($this->exactly(2))->method('delete');

        (new RememberMe($tokens, $this->createMock(UserRepository::class), $cookies))->forget(7);
    }

    private function cookies(?string $user, ?string $token): Cookies
    {
        $cookies = $this->createMock(Cookies::class);
        $cookies->method('get')->willReturnMap([
            ['RMU', $user],
            ['RMT', $token],
        ]);

        return $cookies;
    }

    private function user(): User
    {
        return new User(
            id: 7,
            name: 'Sarah',
            email: 's@x.dev',
            passwordHash: 'h',
            profileId: 2,
            profileName: 'Users',
            active: true,
            banned: false,
            suspended: false,
            mustChangePassword: false
        );
    }
}
