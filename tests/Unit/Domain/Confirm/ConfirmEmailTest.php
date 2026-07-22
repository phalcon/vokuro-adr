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

namespace Vokuro\Tests\Unit\Domain\Confirm;

use Phalcon\ADR\Input\Input;
use Phalcon\ADR\Payload\Status;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Vokuro\Contracts\Repository\EmailConfirmationRepository;
use Vokuro\Contracts\Repository\UserRepository;
use Vokuro\Domain\Confirm\ConfirmEmail;
use Vokuro\Domain\Model\EmailConfirmation;
use Vokuro\Domain\Model\User;

final class ConfirmEmailTest extends AbstractUnitTestCase
{
    /**
     * Unit Tests Vokuro\Domain\Confirm\ConfirmEmail :: reports an unknown code
     */
    public function testUnknownCode(): void
    {
        $confirmations = $this->createMock(EmailConfirmationRepository::class);
        $confirmations->method('findByCode')->willReturn(null);

        $payload = $this->domain($confirmations)(new Input(['code' => 'nope']));

        $this->assertSame(Status::NOT_FOUND, $payload->getStatus());
    }

    /**
     * Unit Tests Vokuro\Domain\Confirm\ConfirmEmail :: refuses an already used code
     */
    public function testAlreadyConfirmed(): void
    {
        $confirmations = $this->createMock(EmailConfirmationRepository::class);
        $confirmations->method('findByCode')->willReturn(new EmailConfirmation(1, 7, true));

        $payload = $this->domain($confirmations)(new Input(['code' => 'used']));

        $this->assertSame(Status::NOT_VALID, $payload->getStatus());
    }

    /**
     * Unit Tests Vokuro\Domain\Confirm\ConfirmEmail :: reports a code whose user is gone
     */
    public function testUserGone(): void
    {
        $confirmations = $this->createMock(EmailConfirmationRepository::class);
        $confirmations->method('findByCode')->willReturn(new EmailConfirmation(1, 7, false));

        $users = $this->createMock(UserRepository::class);
        $users->method('findById')->willReturn(null);

        $payload = $this->domain($confirmations, $users)(new Input(['code' => 'code']));

        $this->assertSame(Status::NOT_FOUND, $payload->getStatus());
    }

    /**
     * Unit Tests Vokuro\Domain\Confirm\ConfirmEmail :: activates the user and marks the code used
     */
    public function testConfirms(): void
    {
        $confirmations = $this->createMock(EmailConfirmationRepository::class);
        $confirmations->method('findByCode')->willReturn(new EmailConfirmation(3, 7, false));
        $confirmations->expects($this->once())->method('markConfirmed')->with(3);

        $users = $this->createMock(UserRepository::class);
        $users->method('findById')->willReturn(
            new User(7, 'Kyle', 'kyle@x.dev', 'h', 2, 'Users', false, false, false, true)
        );
        $users->expects($this->once())->method('update')->with(7, ['active' => 'Y']);

        $payload = $this->domain($confirmations, $users)(new Input(['code' => 'code']));

        $this->assertSame(Status::UPDATED, $payload->getStatus());
        $this->assertSame(
            ['id' => 7, 'name' => 'Kyle', 'email' => 'kyle@x.dev', 'profilesId' => 2, 'mustChangePassword' => true],
            $payload->getResult()
        );
    }

    private function domain(
        ?EmailConfirmationRepository $confirmations = null,
        ?UserRepository $users = null
    ): ConfirmEmail {
        return new ConfirmEmail(
            $confirmations ?? $this->createMock(EmailConfirmationRepository::class),
            $users ?? $this->createMock(UserRepository::class)
        );
    }
}
