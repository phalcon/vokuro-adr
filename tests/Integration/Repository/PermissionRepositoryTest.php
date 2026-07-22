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

use Vokuro\Infrastructure\Repository\PermissionRepository;
use Vokuro\Tests\Integration\AbstractIntegrationTestCase;

final class PermissionRepositoryTest extends AbstractIntegrationTestCase
{
    private PermissionRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clean('permissions');
        $this->repository = new PermissionRepository($this->connection, $this->queryFactory);
    }

    /**
     * Integration Tests Vokuro\Infrastructure\Repository\PermissionRepository :: grantedTo builds the resource.action map
     */
    public function testGrantedToBuildsTheMap(): void
    {
        $this->insert('permissions', ['profilesId' => 2, 'resource' => 'users', 'action' => 'index']);
        $this->insert('permissions', ['profilesId' => 2, 'resource' => 'users', 'action' => 'edit']);

        $this->assertEquals(
            ['users.index' => true, 'users.edit' => true],
            $this->repository->grantedTo(2)
        );
    }

    /**
     * Integration Tests Vokuro\Infrastructure\Repository\PermissionRepository :: grantedTo is empty for an ungranted profile
     */
    public function testGrantedToEmptyProfile(): void
    {
        $this->assertSame([], $this->repository->grantedTo(999));
    }

    /**
     * Integration Tests Vokuro\Infrastructure\Repository\PermissionRepository :: replaceForProfile swaps the grants
     */
    public function testReplaceForProfileReplaces(): void
    {
        $this->insert('permissions', ['profilesId' => 2, 'resource' => 'users', 'action' => 'old']);

        $this->repository->replaceForProfile(2, ['users.index', 'users.edit']);

        $this->assertEquals(
            ['users.index' => true, 'users.edit' => true],
            $this->repository->grantedTo(2)
        );
    }

    /**
     * Integration Tests Vokuro\Infrastructure\Repository\PermissionRepository :: replaceForProfile clears with an empty set
     */
    public function testReplaceForProfileClears(): void
    {
        $this->insert('permissions', ['profilesId' => 2, 'resource' => 'users', 'action' => 'index']);

        $this->repository->replaceForProfile(2, []);

        $this->assertSame([], $this->repository->grantedTo(2));
    }

    /**
     * Integration Tests Vokuro\Infrastructure\Repository\PermissionRepository :: replaceForProfile skips malformed pairs
     */
    public function testReplaceForProfileSkipsMalformedPairs(): void
    {
        $this->repository->replaceForProfile(2, ['nodot', 'users.index']);

        $this->assertEquals(['users.index' => true], $this->repository->grantedTo(2));
    }
}
