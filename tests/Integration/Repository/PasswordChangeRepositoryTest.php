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

use Vokuro\Infrastructure\Repository\PasswordChangeRepository;
use Vokuro\Tests\Integration\AbstractIntegrationTestCase;

final class PasswordChangeRepositoryTest extends AbstractIntegrationTestCase
{
    private PasswordChangeRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clean('password_changes');
        $this->repository = new PasswordChangeRepository($this->connection, $this->queryFactory);
    }

    /**
     * Integration Tests Vokuro\Infrastructure\Repository\PasswordChangeRepository :: add stores a record
     */
    public function testAddStores(): void
    {
        $this->repository->add(7, '1.2.3.4', 'agent');

        $row = $this->connection->fetchOne('SELECT usersId FROM password_changes WHERE ipAddress = \'1.2.3.4\'');
        $this->assertSame(7, (int) $row['usersId']);
    }

    /**
     * Integration Tests Vokuro\Infrastructure\Repository\PasswordChangeRepository :: forUser returns the user's records
     */
    public function testForUserReturnsCollection(): void
    {
        $this->seedChange(7);
        $this->seedChange(7);
        $this->seedChange(9);

        $this->assertCount(2, $this->repository->forUser(7));
    }

    /**
     * Integration Tests Vokuro\Infrastructure\Repository\PasswordChangeRepository :: forUser is empty for an unknown user
     */
    public function testForUserEmpty(): void
    {
        $this->assertCount(0, $this->repository->forUser(999));
    }

    private function seedChange(int $userId): int
    {
        return $this->insert('password_changes', [
            'usersId'   => $userId,
            'ipAddress' => '1.2.3.4',
            'userAgent' => 'agent',
            'createdAt' => 100,
        ]);
    }
}
