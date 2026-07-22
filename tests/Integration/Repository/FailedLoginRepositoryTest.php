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

use Vokuro\Infrastructure\Repository\FailedLoginRepository;
use Vokuro\Tests\Integration\AbstractIntegrationTestCase;

final class FailedLoginRepositoryTest extends AbstractIntegrationTestCase
{
    private FailedLoginRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clean('failed_logins');
        $this->repository = new FailedLoginRepository($this->connection, $this->queryFactory);
    }

    /**
     * Integration Tests Vokuro\Infrastructure\Repository\FailedLoginRepository :: add stores an attempt
     */
    public function testAddStores(): void
    {
        $this->repository->add(7, '1.2.3.4');

        $row = $this->connection->fetchOne('SELECT usersId, ipAddress FROM failed_logins WHERE ipAddress = \'1.2.3.4\'');
        $this->assertSame(7, (int) $row['usersId']);
        $this->assertSame('1.2.3.4', $row['ipAddress']);
    }

    /**
     * Integration Tests Vokuro\Infrastructure\Repository\FailedLoginRepository :: add accepts an unknown user
     */
    public function testAddAcceptsNullUser(): void
    {
        $this->repository->add(null, '1.2.3.4');

        $row = $this->connection->fetchOne('SELECT usersId FROM failed_logins WHERE ipAddress = \'1.2.3.4\'');
        $this->assertNull($row['usersId']);
    }

    /**
     * Integration Tests Vokuro\Infrastructure\Repository\FailedLoginRepository :: recentCount windows by time and address
     */
    public function testRecentCountWindows(): void
    {
        $this->seedAttempt('1.2.3.4', 100);
        $this->seedAttempt('1.2.3.4', 200);
        $this->seedAttempt('1.2.3.4', 300);
        $this->seedAttempt('9.9.9.9', 300);

        $this->assertSame(2, $this->repository->recentCount('1.2.3.4', 200));
    }

    private function seedAttempt(string $ipAddress, int $attempted): int
    {
        return $this->insert('failed_logins', [
            'ipAddress' => $ipAddress,
            'attempted' => $attempted,
        ]);
    }
}
