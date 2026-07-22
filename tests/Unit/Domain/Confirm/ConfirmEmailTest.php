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
use Vokuro\Domain\Confirm\ConfirmEmail;
use Vokuro\Domain\Model\EmailConfirmation;
use Vokuro\Domain\Model\User;
use Vokuro\Tests\Support\Fake\FakeEmailConfirmationRepository;
use Vokuro\Tests\Support\Fake\FakeUserRepository;

final class ConfirmEmailTest extends AbstractUnitTestCase
{
    /**
     * Unit Tests Vokuro\Domain\Confirm\ConfirmEmail :: reports an unknown code
     */
    public function testUnknownCode(): void
    {
        $payload = (new ConfirmEmail(new FakeEmailConfirmationRepository(), new FakeUserRepository()))(
            new Input(['code' => 'nope'])
        );

        $this->assertSame(Status::NOT_FOUND, $payload->getStatus());
    }

    /**
     * Unit Tests Vokuro\Domain\Confirm\ConfirmEmail :: refuses an already used code
     */
    public function testAlreadyConfirmed(): void
    {
        $confirmations = new FakeEmailConfirmationRepository();
        $confirmations->seed('used', new EmailConfirmation(1, 7, true));

        $payload = (new ConfirmEmail($confirmations, new FakeUserRepository()))(new Input(['code' => 'used']));

        $this->assertSame(Status::NOT_VALID, $payload->getStatus());
        $this->assertSame([], $confirmations->confirmed);
    }

    /**
     * Unit Tests Vokuro\Domain\Confirm\ConfirmEmail :: reports a code whose user is gone
     */
    public function testUserGone(): void
    {
        $confirmations = new FakeEmailConfirmationRepository();
        $confirmations->seed('code', new EmailConfirmation(1, 7, false));

        $payload = (new ConfirmEmail($confirmations, new FakeUserRepository()))(new Input(['code' => 'code']));

        $this->assertSame(Status::NOT_FOUND, $payload->getStatus());
    }

    /**
     * Unit Tests Vokuro\Domain\Confirm\ConfirmEmail :: activates the user and marks the code used
     */
    public function testConfirms(): void
    {
        $confirmations = new FakeEmailConfirmationRepository();
        $confirmations->seed('code', new EmailConfirmation(3, 7, false));

        $users = new FakeUserRepository();
        $users->seed(new User(7, 'Kyle', 'kyle@x.dev', 'h', 2, 'Users', false, false, false, true));

        $payload = (new ConfirmEmail($confirmations, $users))(new Input(['code' => 'code']));

        $this->assertSame(Status::UPDATED, $payload->getStatus());
        $this->assertSame(['active' => 'Y'], $users->updated[7]);
        $this->assertSame([3], $confirmations->confirmed);
        $this->assertSame(
            ['id' => 7, 'name' => 'Kyle', 'email' => 'kyle@x.dev', 'profilesId' => 2, 'mustChangePassword' => true],
            $payload->getResult()
        );
    }
}
