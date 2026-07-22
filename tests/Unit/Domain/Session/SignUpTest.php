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
use Vokuro\Contracts\Mailer;
use Vokuro\Contracts\Repository\EmailConfirmationRepository;
use Vokuro\Contracts\Repository\UserRepository;
use Vokuro\Domain\Model\User;
use Vokuro\Domain\Session\SignUp;

final class SignUpTest extends AbstractUnitTestCase
{
    /**
     * Unit Tests Vokuro\Domain\Session\SignUp :: creates the account, confirmation and mail
     */
    public function testCreatesConfirmationAndMails(): void
    {
        $users = $this->createMock(UserRepository::class);
        $users->method('findByEmail')->willReturn(null);
        $users->expects($this->once())->method('add')->willReturn(42);

        $confirmations = $this->createMock(EmailConfirmationRepository::class);
        $confirmations->expects($this->once())->method('add')->willReturn('code');

        $mailer = $this->createMock(Mailer::class);
        $mailer->expects($this->once())->method('send');

        $payload = (new SignUp($users, $confirmations, new Security(), $mailer))(
            $this->input()
        );

        $this->assertSame(Status::CREATED, $payload->getStatus());
        $this->assertSame(['id' => 42, 'email' => 'kyle@resistance.dev'], $payload->getResult());
    }

    /**
     * Unit Tests Vokuro\Domain\Session\SignUp :: rejects and keys each invalid field
     *
     * @dataProvider invalidProvider
     *
     * @param array<string, string> $overrides
     */
    public function testValidation(array $overrides, string $field, bool $duplicate = false): void
    {
        $users = $this->createMock(UserRepository::class);
        $users->method('findByEmail')->willReturn($duplicate ? $this->user() : null);
        $users->expects($this->never())->method('add');

        $payload = (new SignUp(
            $users,
            $this->createMock(EmailConfirmationRepository::class),
            new Security(),
            $this->createMock(Mailer::class)
        ))($this->input($overrides));

        $this->assertSame(Status::NOT_VALID, $payload->getStatus());
        $this->assertArrayHasKey($field, (array) $payload->getMessages());
    }

    /**
     * @return array<string, array{0: array<string, string>, 1: string, 2?: bool}>
     */
    public static function invalidProvider(): array
    {
        return [
            'empty name'        => [['name' => ''], 'name'],
            'empty email'       => [['email' => ''], 'email'],
            'invalid email'     => [['email' => 'nope'], 'email'],
            'duplicate email'   => [[], 'email', true],
            'empty password'    => [['password' => '', 'confirmPassword' => ''], 'password'],
            'short password'    => [['password' => 'short', 'confirmPassword' => 'short'], 'password'],
            'mismatch password' => [['confirmPassword' => 'different'], 'confirmPassword'],
            'terms not accepted' => [['terms' => ''], 'terms'],
        ];
    }

    /**
     * @param array<string, string> $overrides
     */
    private function input(array $overrides = []): Input
    {
        return new Input($overrides + [
            'name'            => 'Kyle Reese',
            'email'           => 'kyle@resistance.dev',
            'password'        => 'abcdefgh',
            'confirmPassword' => 'abcdefgh',
            'terms'           => 'yes',
        ]);
    }

    private function user(): User
    {
        return new User(1, 'x', 'x@x.dev', 'h', 2, 'Users', true, false, false, false);
    }
}
