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
use Vokuro\Domain\Users\CreateUser;
use Vokuro\Tests\Support\Fake\FakeEmailConfirmationRepository;
use Vokuro\Tests\Support\Fake\FakeMailer;
use Vokuro\Tests\Support\Fake\FakeUserRepository;

final class CreateUserTest extends AbstractUnitTestCase
{
    /**
     * Unit Tests Vokuro\Domain\Users\CreateUser :: creates an inactive user, confirmation and mail
     */
    public function testCreatesInactiveUser(): void
    {
        $users         = new FakeUserRepository();
        $confirmations = new FakeEmailConfirmationRepository();
        $mailer        = new FakeMailer();

        $payload = (new CreateUser($users, $confirmations, new Security(), $mailer))(
            new Input(['name' => 'Kate', 'email' => 'kate@x.dev', 'profilesId' => 2])
        );

        $this->assertSame(Status::CREATED, $payload->getStatus());
        $this->assertSame('N', $users->added[1]['active']);
        $this->assertSame('Y', $users->added[1]['mustChangePassword']);
        $this->assertCount(1, $confirmations->added);
        $this->assertCount(1, $mailer->sent);
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
        $users = new FakeUserRepository();

        if (true === $duplicate) {
            $users->seed(new User(1, 'x', 'kate@x.dev', 'h', 2, 'Users', true, false, false, false));
        }

        $payload = (new CreateUser(
            $users,
            new FakeEmailConfirmationRepository(),
            new Security(),
            new FakeMailer()
        ))(new Input($overrides + ['name' => 'Kate', 'email' => 'kate@x.dev', 'profilesId' => 2]));

        $this->assertSame(Status::NOT_VALID, $payload->getStatus());
        $this->assertArrayHasKey($field, (array) $payload->getMessages());
        $this->assertSame([], $users->added);
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
