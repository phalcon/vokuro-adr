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

use Vokuro\Action\Session\PostSessionForgotPassword;
use Vokuro\Contracts\Csrf;
use Vokuro\Domain\Model\User;
use Vokuro\Domain\Session\ForgotPassword;
use Vokuro\Tests\Support\Fake\FakeCsrf;
use Vokuro\Tests\Support\Fake\FakeMailer;
use Vokuro\Tests\Support\Fake\FakeResetPasswordRepository;
use Vokuro\Tests\Support\Fake\FakeUserRepository;
use Vokuro\Tests\Unit\Action\AbstractActionTestCase;

final class PostSessionForgotPasswordTest extends AbstractActionTestCase
{
    private FakeResetPasswordRepository $resets;

    private FakeUserRepository $users;

    protected function setUp(): void
    {
        parent::setUp();

        $this->users  = new FakeUserRepository();
        $this->resets = new FakeResetPasswordRepository();
    }

    /**
     * Unit Tests Vokuro\Action\Session\PostSessionForgotPassword :: a bad CSRF token re-renders the form
     */
    public function testBadCsrfRerendersForm(): void
    {
        $this->action(new FakeCsrf(valid: false))($this->request());

        $this->assertSame('session/forgotPassword', $this->renderer->calls[0]['path']);
        $this->assertSame([], $this->resets->added);
    }

    /**
     * Unit Tests Vokuro\Action\Session\PostSessionForgotPassword :: a known address issues a reset and renders
     */
    public function testKnownAddressIssuesReset(): void
    {
        $this->users->seed(new User(7, 'Sarah', 's@x.dev', 'h', 2, 'Users', true, false, false, false));

        $this->action(new FakeCsrf())($this->request(['email' => 's@x.dev']));

        $this->assertSame('session/forgotPassword', $this->renderer->calls[0]['path']);
        $this->assertCount(1, $this->resets->added);
    }

    private function action(Csrf $csrf): PostSessionForgotPassword
    {
        $domain = new ForgotPassword($this->users, $this->resets, new FakeMailer());

        return new PostSessionForgotPassword($domain, $this->authResponder(), $csrf);
    }
}
