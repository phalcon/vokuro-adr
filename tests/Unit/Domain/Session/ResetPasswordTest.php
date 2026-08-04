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
use Vokuro\Domain\Model\ResetPassword as ResetPasswordRecord;
use Vokuro\Domain\Model\User;
use Vokuro\Domain\Session\ResetPassword;
use Vokuro\Tests\Support\Fake\FakeResetPasswordRepository;
use Vokuro\Tests\Support\Fake\FakeUserRepository;

final class ResetPasswordTest extends AbstractUnitTestCase
{
    /**
     * Unit Tests Vokuro\Domain\Session\ResetPassword :: refuses a code that was already spent
     */
    public function testAlreadyUsed(): void
    {
        $resets = (new FakeResetPasswordRepository())
            ->seed('used', new ResetPasswordRecord(1, 7, 100, true));

        $payload = (new ResetPassword($resets, new FakeUserRepository()))(new Input(['code' => 'used']));

        $this->assertSame(Status::NOT_VALID, $payload->getStatus());
        $this->assertSame([], $resets->spent);
    }

    /**
     * Unit Tests Vokuro\Domain\Session\ResetPassword :: spends the code and hands back the identity
     */
    public function testResets(): void
    {
        $resets = (new FakeResetPasswordRepository())
            ->seed('code', new ResetPasswordRecord(3, 7, 100, false));

        $users = (new FakeUserRepository())
            ->seed(new User(7, 'Kyle', 'kyle@x.dev', 'h', 2, 'Users', true, false, false, false));

        $payload = (new ResetPassword($resets, $users))(new Input(['code' => 'code']));

        $this->assertSame(Status::UPDATED, $payload->getStatus());
        $this->assertSame([3], $resets->spent);
        $this->assertSame(
            ['id' => 7, 'name' => 'Kyle', 'email' => 'kyle@x.dev', 'profilesId' => 2],
            $payload->getResult()
        );
    }

    /**
     * Unit Tests Vokuro\Domain\Session\ResetPassword :: reports an unknown code
     */
    public function testUnknownCode(): void
    {
        $payload = (new ResetPassword(new FakeResetPasswordRepository(), new FakeUserRepository()))(
            new Input(['code' => 'nope'])
        );

        $this->assertSame(Status::NOT_FOUND, $payload->getStatus());
    }

    /**
     * Unit Tests Vokuro\Domain\Session\ResetPassword :: reports a code whose user is gone
     */
    public function testUserGone(): void
    {
        $resets = (new FakeResetPasswordRepository())
            ->seed('code', new ResetPasswordRecord(3, 7, 100, false));

        $payload = (new ResetPassword($resets, new FakeUserRepository()))(new Input(['code' => 'code']));

        $this->assertSame(Status::NOT_FOUND, $payload->getStatus());
        $this->assertSame([], $resets->spent);
    }
}
