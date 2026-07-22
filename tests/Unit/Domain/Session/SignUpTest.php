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
use Vokuro\Domain\Session\SignUp;
use Vokuro\Tests\Support\Fake\FakeEmailConfirmationRepository;
use Vokuro\Tests\Support\Fake\FakeMailer;
use Vokuro\Tests\Support\Fake\FakeUserRepository;

final class SignUpTest extends AbstractUnitTestCase
{
    /**
     * Unit Tests Vokuro\Domain\Session\SignUp :: creates the account, confirmation and mail
     */
    public function testCreatesConfirmationAndMails(): void
    {
        $users         = new FakeUserRepository();
        $confirmations = new FakeEmailConfirmationRepository();
        $mailer        = new FakeMailer();

        $payload = (new SignUp($users, $confirmations, new Security(), $mailer))($this->input());

        $this->assertSame(Status::CREATED, $payload->getStatus());
        $this->assertSame(['id' => 1, 'email' => 'kyle@resistance.dev'], $payload->getResult());
        $this->assertSame('N', $users->added[1]['active']);
        $this->assertCount(1, $confirmations->added);
        $this->assertSame(['kyle@resistance.dev' => 'Kyle Reese'], $mailer->sent[0]['to']);
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
        $users = new FakeUserRepository();

        if (true === $duplicate) {
            $users->seed(new User(1, 'x', 'kyle@resistance.dev', 'h', 2, 'Users', true, false, false, false));
        }

        $payload = (new SignUp(
            $users,
            new FakeEmailConfirmationRepository(),
            new Security(),
            new FakeMailer()
        ))($this->input($overrides));

        $this->assertSame(Status::NOT_VALID, $payload->getStatus());
        $this->assertArrayHasKey($field, (array) $payload->getMessages());
        $this->assertSame([], $users->added);
    }

    /**
     * @return array<string, array{0: array<string, string>, 1: string, 2?: bool}>
     */
    public static function invalidProvider(): array
    {
        return [
            'empty name'         => [['name' => ''], 'name'],
            'empty email'        => [['email' => ''], 'email'],
            'invalid email'      => [['email' => 'nope'], 'email'],
            'duplicate email'    => [[], 'email', true],
            'empty password'     => [['password' => '', 'confirmPassword' => ''], 'password'],
            'short password'     => [['password' => 'short', 'confirmPassword' => 'short'], 'password'],
            'mismatch password'  => [['confirmPassword' => 'different'], 'confirmPassword'],
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
}
