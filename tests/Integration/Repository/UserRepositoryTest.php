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

use Vokuro\Infrastructure\Repository\UserRepository;
use Vokuro\Tests\Integration\AbstractIntegrationTestCase;

final class UserRepositoryTest extends AbstractIntegrationTestCase
{
    private UserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clean('users', 'profiles');
        $this->repository = new UserRepository($this->connection, $this->queryFactory);
    }

    /**
     * Integration Tests Vokuro\Infrastructure\Repository\UserRepository :: add returns the new id and stores the row
     */
    public function testAddStoresAndReturnsId(): void
    {
        $id = $this->repository->add([
            'name'               => 'Sarah',
            'email'              => 's@x.dev',
            'password'           => 'hash',
            'mustChangePassword' => 'N',
            'profilesId'         => 2,
            'banned'             => 'N',
            'suspended'          => 'N',
            'active'             => 'Y',
        ]);

        $this->assertSame(1, $id);

        $row = $this->connection->fetchOne('SELECT email FROM users WHERE id = 1');
        $this->assertSame('s@x.dev', $row['email']);
    }

    /**
     * Integration Tests Vokuro\Infrastructure\Repository\UserRepository :: findById joins the profile name
     */
    public function testFindByIdJoinsProfileName(): void
    {
        $profileId = $this->insert('profiles', ['name' => 'Users', 'active' => 'Y']);
        $userId    = $this->seedUser($profileId);

        $user = $this->repository->findById($userId);

        $this->assertNotNull($user);
        $this->assertSame($userId, $user->id);
        $this->assertSame('Users', $user->profileName);
        $this->assertTrue($user->active);
    }

    /**
     * Integration Tests Vokuro\Infrastructure\Repository\UserRepository :: findById returns null when absent
     */
    public function testFindByIdMissingIsNull(): void
    {
        $this->assertNull($this->repository->findById(999));
    }

    /**
     * Integration Tests Vokuro\Infrastructure\Repository\UserRepository :: findByEmail resolves and misses
     */
    public function testFindByEmail(): void
    {
        $profileId = $this->insert('profiles', ['name' => 'Users', 'active' => 'Y']);
        $this->seedUser($profileId, 's@x.dev');

        $this->assertNotNull($this->repository->findByEmail('s@x.dev'));
        $this->assertNull($this->repository->findByEmail('ghost@x.dev'));
    }

    /**
     * Integration Tests Vokuro\Infrastructure\Repository\UserRepository :: byProfile returns a collection
     */
    public function testByProfileReturnsCollection(): void
    {
        $profileId = $this->insert('profiles', ['name' => 'Users', 'active' => 'Y']);
        $this->seedUser($profileId, 'a@x.dev');
        $this->seedUser($profileId, 'b@x.dev');

        $this->assertCount(2, $this->repository->byProfile($profileId));
    }

    /**
     * Integration Tests Vokuro\Infrastructure\Repository\UserRepository :: update writes the fields
     */
    public function testUpdate(): void
    {
        $profileId = $this->insert('profiles', ['name' => 'Users', 'active' => 'Y']);
        $userId    = $this->seedUser($profileId);

        $this->repository->update($userId, ['banned' => 'Y']);

        $row = $this->connection->fetchOne('SELECT banned FROM users WHERE id = ' . $userId);
        $this->assertSame('Y', $row['banned']);
    }

    /**
     * Integration Tests Vokuro\Infrastructure\Repository\UserRepository :: delete removes the row
     */
    public function testDelete(): void
    {
        $profileId = $this->insert('profiles', ['name' => 'Users', 'active' => 'Y']);
        $userId    = $this->seedUser($profileId);

        $this->repository->delete($userId);

        $this->assertSame([], $this->connection->fetchOne('SELECT id FROM users WHERE id = ' . $userId));
    }

    /**
     * Integration Tests Vokuro\Infrastructure\Repository\UserRepository :: page filters and counts
     */
    public function testPageFilters(): void
    {
        $profileId = $this->insert('profiles', ['name' => 'Users', 'active' => 'Y']);
        $firstId   = $this->seedUser($profileId, 'sarah@x.dev');
        $this->seedUser($profileId, 'kyle@x.dev');
        $this->seedUser($profileId, 'john@x.dev');

        $this->assertSame(1, $this->repository->page(1, 10, ['email' => 'sarah'])->total);
        $this->assertSame(1, $this->repository->page(1, 10, ['id' => $firstId])->total);
        $this->assertSame(3, $this->repository->page(1, 10, ['name' => 'Sarah'])->total);
        $this->assertSame(3, $this->repository->page(1, 10, ['profilesId' => $profileId])->total);
    }

    private function seedUser(int $profilesId, string $email = 's@x.dev'): int
    {
        return $this->insert('users', [
            'name'               => 'Sarah',
            'email'              => $email,
            'password'           => 'hash',
            'mustChangePassword' => 'N',
            'profilesId'         => $profilesId,
            'banned'             => 'N',
            'suspended'          => 'N',
            'active'             => 'Y',
        ]);
    }
}
