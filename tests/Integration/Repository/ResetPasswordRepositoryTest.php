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

use Vokuro\Infrastructure\Repository\ResetPasswordRepository;
use Vokuro\Tests\Integration\AbstractIntegrationTestCase;

final class ResetPasswordRepositoryTest extends AbstractIntegrationTestCase
{
    private ResetPasswordRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clean('reset_passwords');
        $this->repository = new ResetPasswordRepository($this->connection, $this->queryFactory);
    }

    /**
     * Integration Tests Vokuro\Infrastructure\Repository\ResetPasswordRepository :: add stores and returns the code
     */
    public function testAddStores(): void
    {
        $code = $this->repository->add(7, 'abc');

        $this->assertSame('abc', $code);

        $row = $this->connection->fetchOne('SELECT usersId, code FROM reset_passwords WHERE code = \'abc\'');
        $this->assertSame(7, (int) $row['usersId']);
    }

    /**
     * Integration Tests Vokuro\Infrastructure\Repository\ResetPasswordRepository :: forUser returns the user's codes
     */
    public function testForUserReturnsCollection(): void
    {
        $this->seedCode(7, 'a');
        $this->seedCode(7, 'b');
        $this->seedCode(9, 'c');

        $this->assertCount(2, $this->repository->forUser(7));
    }

    /**
     * Integration Tests Vokuro\Infrastructure\Repository\ResetPasswordRepository :: forUser is empty for an unknown user
     */
    public function testForUserEmpty(): void
    {
        $this->assertCount(0, $this->repository->forUser(999));
    }

    private function seedCode(int $userId, string $code): int
    {
        return $this->insert('reset_passwords', [
            'usersId'    => $userId,
            'code'       => $code,
            'createdAt'  => 100,
            'modifiedAt' => 100,
            'reset'      => 'N',
        ]);
    }
}
