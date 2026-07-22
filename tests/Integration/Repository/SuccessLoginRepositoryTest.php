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

use Vokuro\Infrastructure\Repository\SuccessLoginRepository;
use Vokuro\Tests\Integration\AbstractIntegrationTestCase;

final class SuccessLoginRepositoryTest extends AbstractIntegrationTestCase
{
    private SuccessLoginRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clean('success_logins');
        $this->repository = new SuccessLoginRepository($this->connection, $this->queryFactory);
    }

    /**
     * Integration Tests Vokuro\Infrastructure\Repository\SuccessLoginRepository :: add stores a record
     */
    public function testAddStores(): void
    {
        $this->repository->add(7, '1.2.3.4', 'agent');

        $row = $this->connection->fetchOne('SELECT usersId FROM success_logins WHERE ipAddress = \'1.2.3.4\'');
        $this->assertSame(7, (int) $row['usersId']);
    }

    /**
     * Integration Tests Vokuro\Infrastructure\Repository\SuccessLoginRepository :: forUser returns the user's records
     */
    public function testForUserReturnsCollection(): void
    {
        $this->seedLogin(7);
        $this->seedLogin(7);
        $this->seedLogin(9);

        $this->assertCount(2, $this->repository->forUser(7));
    }

    /**
     * Integration Tests Vokuro\Infrastructure\Repository\SuccessLoginRepository :: forUser is empty for an unknown user
     */
    public function testForUserEmpty(): void
    {
        $this->assertCount(0, $this->repository->forUser(999));
    }

    private function seedLogin(int $userId): int
    {
        return $this->insert('success_logins', [
            'usersId'   => $userId,
            'ipAddress' => '1.2.3.4',
            'userAgent' => 'agent',
        ]);
    }
}
