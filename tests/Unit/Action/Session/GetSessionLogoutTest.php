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

namespace Vokuro\Tests\Unit\Action\Session;

use Vokuro\Action\Session\GetSessionLogout;
use Vokuro\Application\RememberMe;
use Vokuro\Tests\Support\Fake\FakeCookies;
use Vokuro\Tests\Support\Fake\FakeRememberTokenRepository;
use Vokuro\Tests\Support\Fake\FakeUserRepository;
use Vokuro\Tests\Unit\Action\AbstractActionTestCase;

final class GetSessionLogoutTest extends AbstractActionTestCase
{
    /**
     * Unit Tests Vokuro\Action\Session\GetSessionLogout :: forgets the token, clears the session and goes home
     */
    public function testSignedInForgetsAndClears(): void
    {
        $this->session->set('auth', ['id' => 7]);
        $tokens = new FakeRememberTokenRepository();

        $response = $this->action($tokens)($this->request());

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('/', $response->getHeaders()->get('Location'));
        $this->assertSame([7], $tokens->deleted);
        $this->assertFalse($this->session->has('auth'));
    }

    /**
     * Unit Tests Vokuro\Action\Session\GetSessionLogout :: a signed out visitor just goes home
     */
    public function testSignedOutJustRedirects(): void
    {
        $tokens = new FakeRememberTokenRepository();

        $response = $this->action($tokens)($this->request());

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame([], $tokens->deleted);
    }

    private function action(FakeRememberTokenRepository $tokens): GetSessionLogout
    {
        $rememberMe = new RememberMe($tokens, new FakeUserRepository(), new FakeCookies());

        return new GetSessionLogout($this->redirectResponder(), $this->session, $rememberMe);
    }
}
