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

namespace Vokuro\Tests\Unit\Action\ResetPassword;

use Vokuro\Action\ResetPassword\GetResetPassword;
use Vokuro\Domain\Model\ResetPassword as ResetPasswordRecord;
use Vokuro\Domain\Model\User;
use Vokuro\Domain\Session\ResetPassword;
use Vokuro\Tests\Support\Fake\FakeResetPasswordRepository;
use Vokuro\Tests\Support\Fake\FakeUserRepository;
use Vokuro\Tests\Unit\Action\AbstractActionTestCase;

final class GetResetPasswordTest extends AbstractActionTestCase
{
    /**
     * Unit Tests Vokuro\Action\ResetPassword\GetResetPassword :: a spent code goes to the login form
     */
    public function testSpentCodeRedirectsToLogin(): void
    {
        $resets = (new FakeResetPasswordRepository())
            ->seed('code', new ResetPasswordRecord(1, 7, 100, true));

        $response = $this->action($resets, new FakeUserRepository())(
            $this->request(attributes: ['code' => 'code'])
        );

        $this->assertSame('/session/login', $response->getHeaders()->get('Location'));
        $this->assertFalse($this->session->has('auth'));
    }

    /**
     * Unit Tests Vokuro\Action\ResetPassword\GetResetPassword :: an unknown code goes home
     */
    public function testUnknownCodeRedirectsHome(): void
    {
        $response = $this->action(new FakeResetPasswordRepository(), new FakeUserRepository())(
            $this->request(attributes: ['code' => 'nope'])
        );

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('/', $response->getHeaders()->get('Location'));
    }

    /**
     * Unit Tests Vokuro\Action\ResetPassword\GetResetPassword :: a live code signs in and opens the form
     */
    public function testValidCodeSignsInAndOpensTheForm(): void
    {
        $resets = (new FakeResetPasswordRepository())
            ->seed('code', new ResetPasswordRecord(3, 7, 100, false));

        $users = (new FakeUserRepository())
            ->seed(new User(7, 'Kyle', 'kyle@x.dev', 'h', 2, 'Users', true, false, false, false));

        $response = $this->action($resets, $users)($this->request(attributes: ['code' => 'code']));

        $this->assertSame('/users/changePassword', $response->getHeaders()->get('Location'));
        $this->assertSame(7, $this->session->get('auth')['id']);
        $this->assertSame([3], $resets->spent);
    }

    private function action(
        FakeResetPasswordRepository $resets,
        FakeUserRepository $users
    ): GetResetPassword {
        return new GetResetPassword(
            new ResetPassword($resets, $users),
            $this->redirectResponder(),
            $this->session
        );
    }
}
