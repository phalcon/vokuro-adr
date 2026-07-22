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
use Vokuro\Domain\Model\User;
use Vokuro\Tests\Support\Fake\FakeCookies;
use Vokuro\Tests\Support\Fake\FakeRememberTokenRepository;
use Vokuro\Tests\Support\Fake\FakeUserRepository;

final class RememberMeTest extends AbstractUnitTestCase
{
    /**
     * Unit Tests Vokuro\Application\RememberMe :: stores a hashed token and sets the cookies
     */
    public function testRememberStoresHashedTokenAndCookies(): void
    {
        $tokens  = new FakeRememberTokenRepository();
        $cookies = new FakeCookies();

        (new RememberMe($tokens, new FakeUserRepository(), $cookies))->remember(7, 'agent');

        $this->assertSame(7, $tokens->added[0]['userId']);
        $this->assertSame('agent', $tokens->added[0]['userAgent']);
        $this->assertSame(64, strlen($tokens->added[0]['tokenHash']));
        $this->assertArrayHasKey('RMU', $cookies->jar);
        $this->assertArrayHasKey('RMT', $cookies->jar);
    }

    /**
     * Unit Tests Vokuro\Application\RememberMe :: recalls a valid cookie into an identity
     */
    public function testRecallReturnsIdentity(): void
    {
        $raw = 'raw-token';

        $tokens = (new FakeRememberTokenRepository())->seed(hash('sha256', $raw), 7);
        $users  = (new FakeUserRepository())->seed($this->user());

        $auth = (new RememberMe($tokens, $users, $this->cookies('7', $raw)))->recall();

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
        // no cookies at all
        $none = new RememberMe(new FakeRememberTokenRepository(), new FakeUserRepository(), new FakeCookies());
        $this->assertNull($none->recall());

        // token unknown to the store
        $unknown = new RememberMe(new FakeRememberTokenRepository(), new FakeUserRepository(), $this->cookies('7', 'raw'));
        $this->assertNull($unknown->recall());

        // token belongs to a different user than the RMU cookie
        $other    = (new FakeRememberTokenRepository())->seed(hash('sha256', 'raw'), 9);
        $mismatch = new RememberMe($other, new FakeUserRepository(), $this->cookies('7', 'raw'));
        $this->assertNull($mismatch->recall());

        // token maps to a user who no longer exists
        $orphan = (new FakeRememberTokenRepository())->seed(hash('sha256', 'raw'), 7);
        $gone   = new RememberMe($orphan, new FakeUserRepository(), $this->cookies('7', 'raw'));
        $this->assertNull($gone->recall());
    }

    /**
     * Unit Tests Vokuro\Application\RememberMe :: forget drops the token and the cookies
     */
    public function testForget(): void
    {
        $tokens  = (new FakeRememberTokenRepository())->seed(hash('sha256', 'raw'), 7);
        $cookies = (new FakeCookies())->seed('RMU', '7')->seed('RMT', 'raw');

        (new RememberMe($tokens, new FakeUserRepository(), $cookies))->forget(7);

        $this->assertSame([7], $tokens->deleted);
        $this->assertContains('RMU', $cookies->deleted);
        $this->assertContains('RMT', $cookies->deleted);
    }

    private function cookies(string $user, string $token): FakeCookies
    {
        return (new FakeCookies())->seed('RMU', $user)->seed('RMT', $token);
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
