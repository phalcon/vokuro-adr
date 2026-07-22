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

namespace Vokuro\Tests\Unit\Domain\Session;

use Phalcon\ADR\Input\Input;
use Phalcon\ADR\Payload\Status;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Vokuro\Contracts\Mailer;
use Vokuro\Contracts\Repository\ResetPasswordRepository;
use Vokuro\Contracts\Repository\UserRepository;
use Vokuro\Domain\Model\User;
use Vokuro\Domain\Session\ForgotPassword;

final class ForgotPasswordTest extends AbstractUnitTestCase
{
    /**
     * Unit Tests Vokuro\Domain\Session\ForgotPassword :: rejects an invalid address
     */
    public function testInvalidEmail(): void
    {
        $payload = $this->domain()(new Input(['email' => 'not-an-email']));

        $this->assertSame(Status::NOT_VALID, $payload->getStatus());
    }

    /**
     * Unit Tests Vokuro\Domain\Session\ForgotPassword :: issues a code and mails a known address
     */
    public function testKnownEmailIssuesAndMails(): void
    {
        $users = $this->createMock(UserRepository::class);
        $users->method('findByEmail')->willReturn($this->user());

        $resets = $this->createMock(ResetPasswordRepository::class);
        $resets->expects($this->once())->method('add')->willReturn('code');

        $mailer = $this->createMock(Mailer::class);
        $mailer->expects($this->once())->method('send');

        $payload = $this->domain($users, $resets, $mailer)(new Input(['email' => 's@x.dev']));

        $this->assertSame(Status::SUCCESS, $payload->getStatus());
    }

    /**
     * Unit Tests Vokuro\Domain\Session\ForgotPassword :: hides whether the address exists
     */
    public function testUnknownEmailGivesTheSameAnswer(): void
    {
        $users = $this->createMock(UserRepository::class);
        $users->method('findByEmail')->willReturn(null);

        $resets = $this->createMock(ResetPasswordRepository::class);
        $resets->expects($this->never())->method('add');

        $mailer = $this->createMock(Mailer::class);
        $mailer->expects($this->never())->method('send');

        $payload = $this->domain($users, $resets, $mailer)(new Input(['email' => 'ghost@x.dev']));

        $this->assertSame(Status::SUCCESS, $payload->getStatus());
    }

    private function domain(
        ?UserRepository $users = null,
        ?ResetPasswordRepository $resets = null,
        ?Mailer $mailer = null
    ): ForgotPassword {
        return new ForgotPassword(
            $users ?? $this->createMock(UserRepository::class),
            $resets ?? $this->createMock(ResetPasswordRepository::class),
            $mailer ?? $this->createMock(Mailer::class)
        );
    }

    private function user(): User
    {
        return new User(1, 'Sarah', 's@x.dev', 'h', 2, 'Users', true, false, false, false);
    }
}
