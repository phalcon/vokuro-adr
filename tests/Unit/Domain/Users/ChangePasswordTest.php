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

namespace Vokuro\Tests\Unit\Domain\Users;

use Phalcon\ADR\Input\Input;
use Phalcon\ADR\Payload\Status;
use Phalcon\Encryption\Security;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Vokuro\Contracts\Repository\PasswordChangeRepository;
use Vokuro\Contracts\Repository\UserRepository;
use Vokuro\Domain\Model\User;
use Vokuro\Domain\Users\ChangePassword;

final class ChangePasswordTest extends AbstractUnitTestCase
{
    /**
     * Unit Tests Vokuro\Domain\Users\ChangePassword :: keys each invalid field
     *
     * @dataProvider invalidProvider
     *
     * @param array<string, mixed> $input
     */
    public function testValidation(array $input, string $field): void
    {
        $payload = $this->domain()(new Input($input + ['userId' => 1]));

        $this->assertSame(Status::NOT_VALID, $payload->getStatus());
        $this->assertArrayHasKey($field, (array) $payload->getMessages());
    }

    /**
     * @return array<string, array{0: array<string, mixed>, 1: string}>
     */
    public static function invalidProvider(): array
    {
        return [
            'empty'    => [['password' => '', 'confirmPassword' => ''], 'password'],
            'short'    => [['password' => 'short', 'confirmPassword' => 'short'], 'password'],
            'mismatch' => [['password' => 'abcdefgh', 'confirmPassword' => 'other'], 'confirmPassword'],
        ];
    }

    /**
     * Unit Tests Vokuro\Domain\Users\ChangePassword :: reports a missing user
     */
    public function testNotFound(): void
    {
        $users = $this->createMock(UserRepository::class);
        $users->method('findById')->willReturn(null);

        $payload = $this->domain($users)(
            new Input(['userId' => 1, 'password' => 'abcdefgh', 'confirmPassword' => 'abcdefgh'])
        );

        $this->assertSame(Status::NOT_FOUND, $payload->getStatus());
    }

    /**
     * Unit Tests Vokuro\Domain\Users\ChangePassword :: updates the hash and records the change
     */
    public function testChangesAndRecords(): void
    {
        $users = $this->createMock(UserRepository::class);
        $users->method('findById')->willReturn(
            new User(1, 'Sarah', 's@x.dev', 'h', 2, 'Users', true, false, false, true)
        );
        $users->expects($this->once())->method('update')
              ->with(1, $this->callback(fn(array $f): bool => 'N' === $f['mustChangePassword']));

        $changes = $this->createMock(PasswordChangeRepository::class);
        $changes->expects($this->once())->method('add')->with(1, 'ip', 'agent');

        $payload = $this->domain($users, $changes)(new Input([
            'userId'          => 1,
            'password'        => 'newpassword1',
            'confirmPassword' => 'newpassword1',
            'ipAddress'       => 'ip',
            'userAgent'       => 'agent',
        ]));

        $this->assertSame(Status::UPDATED, $payload->getStatus());
    }

    private function domain(
        ?UserRepository $users = null,
        ?PasswordChangeRepository $changes = null
    ): ChangePassword {
        return new ChangePassword(
            $users ?? $this->createMock(UserRepository::class),
            $changes ?? $this->createMock(PasswordChangeRepository::class),
            new Security()
        );
    }
}
