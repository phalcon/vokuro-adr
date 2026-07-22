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
use Phalcon\Encryption\Security;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Vokuro\Domain\Model\User;
use Vokuro\Domain\Session\Login;
use Vokuro\Tests\Support\Fake\FakeFailedLoginRepository;
use Vokuro\Tests\Support\Fake\FakeSuccessLoginRepository;
use Vokuro\Tests\Support\Fake\FakeUserRepository;

final class LoginTest extends AbstractUnitTestCase
{
    private Security $security;

    protected function setUp(): void
    {
        parent::setUp();
        $this->security = new Security();
    }

    /**
     * Unit Tests Vokuro\Domain\Session\Login :: rejects empty credentials
     */
    public function testEmptyCredentials(): void
    {
        $payload = $this->login()(new Input(['email' => '', 'password' => '']));

        $this->assertSame(Status::NOT_VALID, $payload->getStatus());
    }

    /**
     * Unit Tests Vokuro\Domain\Session\Login :: blocks an address over the throttle limit
     */
    public function testThrottled(): void
    {
        $failed         = new FakeFailedLoginRepository();
        $failed->recent = 5;

        $payload = $this->login(failed: $failed)(
            new Input(['email' => 'x@x.dev', 'password' => 'secret', 'ipAddress' => 'ip'])
        );

        $this->assertSame(Status::NOT_AUTHORIZED, $payload->getStatus());
    }

    /**
     * Unit Tests Vokuro\Domain\Session\Login :: records and rejects a wrong password
     */
    public function testWrongPassword(): void
    {
        $users = (new FakeUserRepository())->seed($this->user('secret'));

        $failed = new FakeFailedLoginRepository();

        $payload = $this->login($users, failed: $failed)(
            new Input(['email' => 'x@x.dev', 'password' => 'wrong', 'ipAddress' => 'ip'])
        );

        $this->assertSame(Status::NOT_AUTHENTICATED, $payload->getStatus());
        $this->assertSame(['userId' => 7, 'ipAddress' => 'ip'], $failed->added[0]);
    }

    /**
     * Unit Tests Vokuro\Domain\Session\Login :: records a failure for an unknown address
     */
    public function testUnknownEmail(): void
    {
        $failed = new FakeFailedLoginRepository();

        $payload = $this->login(new FakeUserRepository(), failed: $failed)(
            new Input(['email' => 'x@x.dev', 'password' => 'secret', 'ipAddress' => 'ip'])
        );

        $this->assertSame(Status::NOT_AUTHENTICATED, $payload->getStatus());
        $this->assertSame(['userId' => null, 'ipAddress' => 'ip'], $failed->added[0]);
    }

    /**
     * Unit Tests Vokuro\Domain\Session\Login :: refuses an inactive account
     */
    public function testInactive(): void
    {
        $users = (new FakeUserRepository())->seed($this->user('secret', active: false));

        $payload = $this->login($users)(
            new Input(['email' => 'x@x.dev', 'password' => 'secret', 'ipAddress' => 'ip'])
        );

        $this->assertSame(Status::NOT_AUTHENTICATED, $payload->getStatus());
    }

    /**
     * Unit Tests Vokuro\Domain\Session\Login :: refuses a banned account
     */
    public function testBanned(): void
    {
        $users = (new FakeUserRepository())->seed($this->user('secret', banned: true));

        $payload = $this->login($users)(
            new Input(['email' => 'x@x.dev', 'password' => 'secret', 'ipAddress' => 'ip'])
        );

        $this->assertSame(Status::NOT_AUTHORIZED, $payload->getStatus());
    }

    /**
     * Unit Tests Vokuro\Domain\Session\Login :: authenticates and records a valid sign in
     */
    public function testSuccess(): void
    {
        $users   = (new FakeUserRepository())->seed($this->user('secret'));
        $success = new FakeSuccessLoginRepository();

        $payload = $this->login($users, $success)(
            new Input(['email' => 'x@x.dev', 'password' => 'secret', 'ipAddress' => 'ip', 'userAgent' => 'agent'])
        );

        $this->assertSame(Status::AUTHENTICATED, $payload->getStatus());
        $this->assertSame(['userId' => 7, 'ipAddress' => 'ip', 'userAgent' => 'agent'], $success->added[0]);
        $this->assertSame(
            ['id' => 7, 'name' => 'Sarah', 'email' => 'x@x.dev', 'profilesId' => 2],
            $payload->getResult()
        );
    }

    private function login(
        ?FakeUserRepository $users = null,
        ?FakeSuccessLoginRepository $success = null,
        ?FakeFailedLoginRepository $failed = null
    ): Login {
        return new Login(
            $users ?? new FakeUserRepository(),
            $success ?? new FakeSuccessLoginRepository(),
            $failed ?? new FakeFailedLoginRepository(),
            $this->security
        );
    }

    private function user(string $password, bool $active = true, bool $banned = false): User
    {
        return new User(
            id: 7,
            name: 'Sarah',
            email: 'x@x.dev',
            passwordHash: $this->security->hash($password),
            profileId: 2,
            profileName: 'Users',
            active: $active,
            banned: $banned,
            suspended: false,
            mustChangePassword: false
        );
    }
}
