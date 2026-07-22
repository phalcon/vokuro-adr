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

namespace Vokuro\Tests\Integration\Repository;

use Vokuro\Infrastructure\Repository\RememberTokenRepository;
use Vokuro\Tests\Integration\AbstractIntegrationTestCase;

final class RememberTokenRepositoryTest extends AbstractIntegrationTestCase
{
    private RememberTokenRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clean('remember_tokens');
        $this->repository = new RememberTokenRepository($this->connection, $this->queryFactory);
    }

    /**
     * Integration Tests Vokuro\Infrastructure\Repository\RememberTokenRepository :: add stores the token
     */
    public function testAddStores(): void
    {
        $this->repository->add(7, 'hash', 'agent');

        $row = $this->connection->fetchOne('SELECT usersId, token FROM remember_tokens WHERE token = \'hash\'');
        $this->assertSame(7, (int) $row['usersId']);
    }

    /**
     * Integration Tests Vokuro\Infrastructure\Repository\RememberTokenRepository :: findUserByToken resolves the owner
     */
    public function testFindUserByToken(): void
    {
        $this->seedToken(7, 'hash');

        $this->assertSame(7, $this->repository->findUserByToken('hash'));
    }

    /**
     * Integration Tests Vokuro\Infrastructure\Repository\RememberTokenRepository :: findUserByToken misses as null
     */
    public function testFindUserByTokenMissingIsNull(): void
    {
        $this->assertNull($this->repository->findUserByToken('nope'));
    }

    /**
     * Integration Tests Vokuro\Infrastructure\Repository\RememberTokenRepository :: deleteForUser drops only that user's tokens
     */
    public function testDeleteForUser(): void
    {
        $this->seedToken(7, 'hash-a');
        $this->seedToken(7, 'hash-b');
        $this->seedToken(9, 'hash-c');

        $this->repository->deleteForUser(7);

        $this->assertNull($this->repository->findUserByToken('hash-a'));
        $this->assertSame(9, $this->repository->findUserByToken('hash-c'));
    }

    private function seedToken(int $userId, string $token): int
    {
        return $this->insert('remember_tokens', [
            'usersId'   => $userId,
            'token'     => $token,
            'userAgent' => 'agent',
            'createdAt' => 100,
        ]);
    }
}
