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

use Vokuro\Infrastructure\Repository\ProfileRepository;
use Vokuro\Tests\Integration\AbstractIntegrationTestCase;

final class ProfileRepositoryTest extends AbstractIntegrationTestCase
{
    private ProfileRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clean('profiles');
        $this->repository = new ProfileRepository($this->connection, $this->queryFactory);
    }

    /**
     * Integration Tests Vokuro\Infrastructure\Repository\ProfileRepository :: add returns the new id and stores the row
     */
    public function testAddStoresAndReturnsId(): void
    {
        $id = $this->repository->add(['name' => 'Auditors', 'active' => 'Y']);

        $this->assertSame(1, $id);

        $row = $this->connection->fetchOne('SELECT name, active FROM profiles WHERE id = 1');
        $this->assertSame('Auditors', $row['name']);
        $this->assertSame('Y', $row['active']);
    }

    /**
     * Integration Tests Vokuro\Infrastructure\Repository\ProfileRepository :: findById hydrates a profile
     */
    public function testFindByIdHydrates(): void
    {
        $id = $this->insert('profiles', ['name' => 'Admins', 'active' => 'Y']);

        $profile = $this->repository->findById($id);

        $this->assertNotNull($profile);
        $this->assertSame($id, $profile->id);
        $this->assertSame('Admins', $profile->name);
        $this->assertTrue($profile->active);
    }

    /**
     * Integration Tests Vokuro\Infrastructure\Repository\ProfileRepository :: findById returns null when absent
     */
    public function testFindByIdMissingIsNull(): void
    {
        $this->assertNull($this->repository->findById(999));
    }

    /**
     * Integration Tests Vokuro\Infrastructure\Repository\ProfileRepository :: update writes the fields
     */
    public function testUpdate(): void
    {
        $id = $this->insert('profiles', ['name' => 'Admins', 'active' => 'Y']);

        $this->repository->update($id, ['name' => 'Managers', 'active' => 'N']);

        $row = $this->connection->fetchOne('SELECT name, active FROM profiles WHERE id = ' . $id);
        $this->assertSame('Managers', $row['name']);
        $this->assertSame('N', $row['active']);
    }

    /**
     * Integration Tests Vokuro\Infrastructure\Repository\ProfileRepository :: delete removes the row
     */
    public function testDelete(): void
    {
        $id = $this->insert('profiles', ['name' => 'Admins', 'active' => 'Y']);

        $this->repository->delete($id);

        $this->assertSame([], $this->connection->fetchOne('SELECT id FROM profiles WHERE id = ' . $id));
    }

    /**
     * Integration Tests Vokuro\Infrastructure\Repository\ProfileRepository :: listForSelect returns id => name ordered by name
     */
    public function testListForSelect(): void
    {
        $this->insert('profiles', ['name' => 'Users', 'active' => 'Y']);
        $this->insert('profiles', ['name' => 'Admins', 'active' => 'Y']);

        $this->assertSame([2 => 'Admins', 1 => 'Users'], $this->repository->listForSelect());
    }

    /**
     * Integration Tests Vokuro\Infrastructure\Repository\ProfileRepository :: page returns a filtered, counted page
     */
    public function testPageFiltersAndCounts(): void
    {
        $this->insert('profiles', ['name' => 'Admins', 'active' => 'Y']);
        $this->insert('profiles', ['name' => 'Auditors', 'active' => 'Y']);
        $this->insert('profiles', ['name' => 'Users', 'active' => 'Y']);

        $page = $this->repository->page(1, 10, ['name' => 'Aud']);

        $this->assertSame(1, $page->total);
        $this->assertCount(1, $page->items);
    }
}
