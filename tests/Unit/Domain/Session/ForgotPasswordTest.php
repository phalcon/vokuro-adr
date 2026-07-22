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
use Vokuro\Domain\Model\User;
use Vokuro\Domain\Session\ForgotPassword;
use Vokuro\Tests\Support\Fake\FakeMailer;
use Vokuro\Tests\Support\Fake\FakeResetPasswordRepository;
use Vokuro\Tests\Support\Fake\FakeUserRepository;

final class ForgotPasswordTest extends AbstractUnitTestCase
{
    /**
     * Unit Tests Vokuro\Domain\Session\ForgotPassword :: rejects an invalid address
     */
    public function testInvalidEmail(): void
    {
        $resets = new FakeResetPasswordRepository();
        $mailer = new FakeMailer();

        $payload = (new ForgotPassword(new FakeUserRepository(), $resets, $mailer))(
            new Input(['email' => 'not-an-email'])
        );

        $this->assertSame(Status::NOT_VALID, $payload->getStatus());
        $this->assertSame([], $resets->added);
        $this->assertSame([], $mailer->sent);
    }

    /**
     * Unit Tests Vokuro\Domain\Session\ForgotPassword :: issues a code and mails a known address
     */
    public function testKnownEmailIssuesAndMails(): void
    {
        $users = new FakeUserRepository();
        $users->seed(new User(7, 'Sarah', 's@x.dev', 'h', 2, 'Users', true, false, false, false));

        $resets = new FakeResetPasswordRepository();
        $mailer = new FakeMailer();

        $payload = (new ForgotPassword($users, $resets, $mailer))(new Input(['email' => 's@x.dev']));

        $this->assertSame(Status::SUCCESS, $payload->getStatus());
        $this->assertSame(7, $resets->added[0]['userId']);
        $this->assertSame(['s@x.dev' => 'Sarah'], $mailer->sent[0]['to']);
    }

    /**
     * Unit Tests Vokuro\Domain\Session\ForgotPassword :: hides whether the address exists
     */
    public function testUnknownEmailGivesTheSameAnswer(): void
    {
        $resets = new FakeResetPasswordRepository();
        $mailer = new FakeMailer();

        $payload = (new ForgotPassword(new FakeUserRepository(), $resets, $mailer))(
            new Input(['email' => 'ghost@x.dev'])
        );

        $this->assertSame(Status::SUCCESS, $payload->getStatus());
        $this->assertSame([], $resets->added);
        $this->assertSame([], $mailer->sent);
    }
}
