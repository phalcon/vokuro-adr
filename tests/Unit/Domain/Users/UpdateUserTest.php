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
use Vokuro\Contracts\Repository\UserRepository;
use Vokuro\Domain\Model\User;
use Vokuro\Domain\Users\UpdateUser;

final class UpdateUserTest extends AbstractUnitTestCase
{
    /**
     * Unit Tests Vokuro\Domain\Users\UpdateUser :: reports a missing user
     */
    public function testNotFound(): void
    {
        $users = $this->createMock(UserRepository::class);
        $users->method('findById')->willReturn(null);

        $payload = (new UpdateUser($users))(new Input(['id' => 99]));

        $this->assertSame(Status::NOT_FOUND, $payload->getStatus());
    }

    /**
     * Unit Tests Vokuro\Domain\Users\UpdateUser :: rejects an e-mail owned by another user
     */
    public function testEmailCollision(): void
    {
        $users = $this->createMock(UserRepository::class);
        $users->method('findById')->willReturn($this->user(1));
        $users->method('findByEmail')->willReturn($this->user(2));

        $payload = (new UpdateUser($users))(
            new Input(['id' => 1, 'name' => 'Sarah', 'email' => 'taken@x.dev', 'profilesId' => 2])
        );

        $this->assertSame(Status::NOT_VALID, $payload->getStatus());
        $this->assertArrayHasKey('email', (array) $payload->getMessages());
    }

    /**
     * Unit Tests Vokuro\Domain\Users\UpdateUser :: normalises the flags and saves
     */
    public function testUpdatesAndNormalisesFlags(): void
    {
        $users = $this->createMock(UserRepository::class);
        $users->method('findById')->willReturn($this->user(1));
        $users->method('findByEmail')->willReturn($this->user(1));
        $users->expects($this->once())->method('update')
              ->with(1, $this->callback(
                  fn(array $f): bool => 'Y' === $f['banned'] && 'N' === $f['suspended'] && 'N' === $f['active']
              ));

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
    }

    private function user(int $id): User
    {
        return new User($id, 'Sarah', 's@x.dev', 'h', 2, 'Users', true, false, false, false);
    }
}
