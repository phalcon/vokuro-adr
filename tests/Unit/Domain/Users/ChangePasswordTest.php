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
use Vokuro\Domain\Model\User;
use Vokuro\Domain\Users\ChangePassword;
use Vokuro\Tests\Support\Fake\FakePasswordChangeRepository;
use Vokuro\Tests\Support\Fake\FakeUserRepository;

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
        $users   = new FakeUserRepository();
        $changes = new FakePasswordChangeRepository();

        $payload = (new ChangePassword($users, $changes, new Security()))(
            new Input($input + ['userId' => 1])
        );

        $this->assertSame(Status::NOT_VALID, $payload->getStatus());
        $this->assertArrayHasKey($field, (array) $payload->getMessages());
        $this->assertSame([], $users->updated);
        $this->assertSame([], $changes->added);
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
        $users = new FakeUserRepository();

        $payload = (new ChangePassword($users, new FakePasswordChangeRepository(), new Security()))(
            new Input(['userId' => 1, 'password' => 'abcdefgh', 'confirmPassword' => 'abcdefgh'])
        );

        $this->assertSame(Status::NOT_FOUND, $payload->getStatus());
    }

    /**
     * Unit Tests Vokuro\Domain\Users\ChangePassword :: updates the hash and records the change
     */
    public function testChangesAndRecords(): void
    {
        $users = new FakeUserRepository();
        $users->seed(new User(1, 'Sarah', 's@x.dev', 'h', 2, 'Users', true, false, false, true));

        $changes = new FakePasswordChangeRepository();

        $payload = (new ChangePassword($users, $changes, new Security()))(new Input([
            'userId'          => 1,
            'password'        => 'newpassword1',
            'confirmPassword' => 'newpassword1',
            'ipAddress'       => 'ip',
            'userAgent'       => 'agent',
        ]));

        $this->assertSame(Status::UPDATED, $payload->getStatus());
        $this->assertSame('N', $users->updated[1]['mustChangePassword']);
        $this->assertSame(['userId' => 1, 'ipAddress' => 'ip', 'userAgent' => 'agent'], $changes->added[0]);
    }
}
