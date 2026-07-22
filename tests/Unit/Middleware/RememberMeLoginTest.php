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

namespace Vokuro\Tests\Unit\Middleware;

use Phalcon\Http\Request;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Vokuro\Application\RememberMe;
use Vokuro\Domain\Model\User;
use Vokuro\Middleware\RememberMeLogin;
use Vokuro\Tests\Support\Fake\FakeCookies;
use Vokuro\Tests\Support\Fake\FakeHandler;
use Vokuro\Tests\Support\Fake\FakeRememberTokenRepository;
use Vokuro\Tests\Support\Fake\FakeSession;
use Vokuro\Tests\Support\Fake\FakeUserRepository;

final class RememberMeLoginTest extends AbstractUnitTestCase
{
    /**
     * Unit Tests Vokuro\Middleware\RememberMeLogin :: restores a session from the cookie
     */
    public function testRestoresSession(): void
    {
        $session    = new FakeSession();
        $rememberMe = $this->rememberMe(7, 'raw');

        (new RememberMeLogin($session, $rememberMe))(new Request(), new FakeHandler());

        $this->assertSame(
            ['id' => 7, 'name' => 'Sarah', 'email' => 's@x.dev', 'profilesId' => 2],
            $session->get('auth')
        );
    }

    /**
     * Unit Tests Vokuro\Middleware\RememberMeLogin :: leaves an existing session alone
     */
    public function testLeavesExistingSession(): void
    {
        $session    = new FakeSession(['auth' => ['id' => 1]]);
        $rememberMe = $this->rememberMe(9, 'raw');

        (new RememberMeLogin($session, $rememberMe))(new Request(), new FakeHandler());

        $this->assertSame(['id' => 1], $session->get('auth'));
    }

    /**
     * Unit Tests Vokuro\Middleware\RememberMeLogin :: does nothing without a valid cookie
     */
    public function testNoCookieNoSession(): void
    {
        $session    = new FakeSession();
        $rememberMe = new RememberMe(
            new FakeRememberTokenRepository(),
            new FakeUserRepository(),
            new FakeCookies()
        );

        (new RememberMeLogin($session, $rememberMe))(new Request(), new FakeHandler());

        $this->assertFalse($session->has('auth'));
    }

    private function rememberMe(int $userId, string $raw): RememberMe
    {
        $cookies = (new FakeCookies())->seed('RMU', (string) $userId)->seed('RMT', $raw);
        $tokens  = (new FakeRememberTokenRepository())->seed(hash('sha256', $raw), $userId);
        $users   = (new FakeUserRepository())->seed(
            new User($userId, 'Sarah', 's@x.dev', 'h', 2, 'Users', true, false, false, false)
        );

        return new RememberMe($tokens, $users, $cookies);
    }
}
