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
use Vokuro\Contracts\Mailer;
use Vokuro\Contracts\Repository\EmailConfirmationRepository;
use Vokuro\Contracts\Repository\UserRepository;
use Vokuro\Domain\Model\User;
use Vokuro\Domain\Users\CreateUser;

final class CreateUserTest extends AbstractUnitTestCase
{
    /**
     * Unit Tests Vokuro\Domain\Users\CreateUser :: creates an inactive user, confirmation and mail
     */
    public function testCreatesInactiveUser(): void
    {
        $users = $this->createMock(UserRepository::class);
        $users->method('findByEmail')->willReturn(null);
        $users->expects($this->once())->method('add')
              ->with($this->callback(fn(array $u): bool => 'N' === $u['active'] && 'Y' === $u['mustChangePassword']))
              ->willReturn(9);

        $confirmations = $this->createMock(EmailConfirmationRepository::class);
        $confirmations->expects($this->once())->method('add')->willReturn('code');

        $mailer = $this->createMock(Mailer::class);
        $mailer->expects($this->once())->method('send');

        $payload = (new CreateUser($users, $confirmations, new Security(), $mailer))(
            new Input(['name' => 'Kate', 'email' => 'kate@x.dev', 'profilesId' => 2])
        );

        $this->assertSame(Status::CREATED, $payload->getStatus());
    }

    /**
     * Unit Tests Vokuro\Domain\Users\CreateUser :: keys each invalid field
     *
     * @dataProvider invalidProvider
     *
     * @param array<string, mixed> $overrides
     */
    public function testValidation(array $overrides, string $field, bool $duplicate = false): void
    {
        $users = $this->createMock(UserRepository::class);
        $users->method('findByEmail')->willReturn(
            $duplicate ? new User(1, 'x', 'x@x.dev', 'h', 2, 'Users', true, false, false, false) : null
        );
        $users->expects($this->never())->method('add');

        $payload = (new CreateUser(
            $users,
            $this->createMock(EmailConfirmationRepository::class),
            new Security(),
            $this->createMock(Mailer::class)
        ))(new Input($overrides + ['name' => 'Kate', 'email' => 'kate@x.dev', 'profilesId' => 2]));

        $this->assertSame(Status::NOT_VALID, $payload->getStatus());
        $this->assertArrayHasKey($field, (array) $payload->getMessages());
    }

    /**
     * @return array<string, array{0: array<string, mixed>, 1: string, 2?: bool}>
     */
    public static function invalidProvider(): array
    {
        return [
            'empty name'      => [['name' => ''], 'name'],
            'empty email'     => [['email' => ''], 'email'],
            'invalid email'   => [['email' => 'nope'], 'email'],
            'duplicate email' => [[], 'email', true],
            'no profile'      => [['profilesId' => 0], 'profilesId'],
        ];
    }
}
