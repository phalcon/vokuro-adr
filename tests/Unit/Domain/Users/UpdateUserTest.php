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
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Vokuro\Domain\Model\User;
use Vokuro\Domain\Users\UpdateUser;
use Vokuro\Tests\Support\Fake\FakeUserRepository;

final class UpdateUserTest extends AbstractUnitTestCase
{
    /**
     * Unit Tests Vokuro\Domain\Users\UpdateUser :: reports a missing user
     */
    public function testNotFound(): void
    {
        $users = new FakeUserRepository();

        $payload = (new UpdateUser($users))(new Input(['id' => 99]));

        $this->assertSame(Status::NOT_FOUND, $payload->getStatus());
        $this->assertSame([], $users->updated);
    }

    /**
     * Unit Tests Vokuro\Domain\Users\UpdateUser :: rejects an e-mail owned by another user
     */
    public function testEmailCollision(): void
    {
        $users = new FakeUserRepository();
        $users->seed($this->user(1, 's@x.dev'));
        $users->seed($this->user(2, 'taken@x.dev'));

        $payload = (new UpdateUser($users))(
            new Input(['id' => 1, 'name' => 'Sarah', 'email' => 'taken@x.dev', 'profilesId' => 2])
        );

        $this->assertSame(Status::NOT_VALID, $payload->getStatus());
        $this->assertArrayHasKey('email', (array) $payload->getMessages());
        $this->assertSame([], $users->updated);
    }

    /**
     * Unit Tests Vokuro\Domain\Users\UpdateUser :: normalises the flags and saves
     */
    public function testUpdatesAndNormalisesFlags(): void
    {
        $users = new FakeUserRepository();
        $users->seed($this->user(1, 's@x.dev'));

        $payload = (new UpdateUser($users))(new Input([
            'id'         => 1,
            'name'       => 'Sarah',
            'email'      => 's@x.dev',
            'profilesId' => 2,
            'banned'     => 'Y',
            'suspended'  => 'no',
            'active'     => '',
        ]));

        $this->assertSame(Status::UPDATED, $payload->getStatus());
        $this->assertSame('Y', $users->updated[1]['banned']);
        $this->assertSame('N', $users->updated[1]['suspended']);
        $this->assertSame('N', $users->updated[1]['active']);
    }

    /**
     * Unit Tests Vokuro\Domain\Users\UpdateUser :: keys each invalid field
     *
     * @dataProvider invalidProvider
     *
     * @param array<string, mixed> $overrides
     */
    public function testValidation(array $overrides, string $field): void
    {
        $users = new FakeUserRepository();
        $users->seed($this->user(1, 's@x.dev'));

        $payload = (new UpdateUser($users))(new Input($overrides + [
            'id'         => 1,
            'name'       => 'Sarah',
            'email'      => 's@x.dev',
            'profilesId' => 2,
        ]));

        $this->assertSame(Status::NOT_VALID, $payload->getStatus());
        $this->assertArrayHasKey($field, (array) $payload->getMessages());
        $this->assertSame([], $users->updated);
    }

    /**
     * @return array<string, array{0: array<string, mixed>, 1: string}>
     */
    public static function invalidProvider(): array
    {
        return [
            'empty name'    => [['name' => ''], 'name'],
            'empty email'   => [['email' => ''], 'email'],
            'invalid email' => [['email' => 'bad'], 'email'],
            'no profile'    => [['profilesId' => 0], 'profilesId'],
        ];
    }

    private function user(int $id, string $email): User
    {
        return new User($id, 'Sarah', $email, 'h', 2, 'Users', true, false, false, false);
    }
}
