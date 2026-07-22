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

namespace Vokuro\Tests\Unit\Action\Confirm;

use Vokuro\Action\Confirm\GetConfirm;
use Vokuro\Domain\Confirm\ConfirmEmail;
use Vokuro\Domain\Model\EmailConfirmation;
use Vokuro\Domain\Model\User;
use Vokuro\Tests\Support\Fake\FakeEmailConfirmationRepository;
use Vokuro\Tests\Support\Fake\FakeUserRepository;
use Vokuro\Tests\Unit\Action\AbstractActionTestCase;

final class GetConfirmTest extends AbstractActionTestCase
{
    /**
     * Unit Tests Vokuro\Action\Confirm\GetConfirm :: an unknown code goes home
     */
    public function testUnknownCodeRedirectsHome(): void
    {
        $response = $this->action(new FakeEmailConfirmationRepository(), new FakeUserRepository())(
            $this->request(attributes: [0 => 'nope'])
        );

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('/', $response->getHeaders()->get('Location'));
    }

    /**
     * Unit Tests Vokuro\Action\Confirm\GetConfirm :: an already used code goes to the login form
     */
    public function testAlreadyConfirmedRedirectsToLogin(): void
    {
        $confirmations = (new FakeEmailConfirmationRepository())
            ->seed('code', new EmailConfirmation(1, 7, true));

        $response = $this->action($confirmations, new FakeUserRepository())(
            $this->request(attributes: [0 => 'code'])
        );

        $this->assertSame('/session/login', $response->getHeaders()->get('Location'));
    }

    /**
     * Unit Tests Vokuro\Action\Confirm\GetConfirm :: a fresh code signs in and sends a must-change user on
     */
    public function testConfirmsAndSendsToChangePassword(): void
    {
        $response = $this->action(
            (new FakeEmailConfirmationRepository())->seed('code', new EmailConfirmation(1, 7, false)),
            (new FakeUserRepository())->seed($this->user(true))
        )($this->request(attributes: [0 => 'code']));

        $this->assertSame('/users/changePassword', $response->getHeaders()->get('Location'));
        $this->assertSame(7, $this->session->get('auth')['id']);
    }

    /**
     * Unit Tests Vokuro\Action\Confirm\GetConfirm :: a fresh code signs in and sends a normal user to the area
     */
    public function testConfirmsAndSendsToUsers(): void
    {
        $response = $this->action(
            (new FakeEmailConfirmationRepository())->seed('code', new EmailConfirmation(1, 7, false)),
            (new FakeUserRepository())->seed($this->user(false))
        )($this->request(attributes: [0 => 'code']));

        $this->assertSame('/users', $response->getHeaders()->get('Location'));
    }

    private function action(
        FakeEmailConfirmationRepository $confirmations,
        FakeUserRepository $users
    ): GetConfirm {
        return new GetConfirm(
            new ConfirmEmail($confirmations, $users),
            $this->redirectResponder(),
            $this->session
        );
    }

    private function user(bool $mustChangePassword): User
    {
        return new User(7, 'Kyle', 'kyle@x.dev', 'h', 2, 'Users', false, false, false, $mustChangePassword);
    }
}
