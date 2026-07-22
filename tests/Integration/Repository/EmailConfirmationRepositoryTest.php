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

use Vokuro\Infrastructure\Repository\EmailConfirmationRepository;
use Vokuro\Tests\Integration\AbstractIntegrationTestCase;

final class EmailConfirmationRepositoryTest extends AbstractIntegrationTestCase
{
    private EmailConfirmationRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clean('email_confirmations');
        $this->repository = new EmailConfirmationRepository($this->connection, $this->queryFactory);
    }

    /**
     * Integration Tests Vokuro\Infrastructure\Repository\EmailConfirmationRepository :: add stores an unconfirmed code
     */
    public function testAddStoresUnconfirmed(): void
    {
        $code = $this->repository->add(7, 'abc');

        $this->assertSame('abc', $code);

        $row = $this->connection->fetchOne('SELECT usersId, confirmed FROM email_confirmations WHERE code = \'abc\'');
        $this->assertSame(7, (int) $row['usersId']);
        $this->assertSame('N', $row['confirmed']);
    }

    /**
     * Integration Tests Vokuro\Infrastructure\Repository\EmailConfirmationRepository :: findByCode hydrates
     */
    public function testFindByCodeHydrates(): void
    {
        $this->insert('email_confirmations', [
            'usersId'   => 7,
            'code'      => 'abc',
            'createdAt' => 100,
            'confirmed' => 'N',
        ]);

        $confirmation = $this->repository->findByCode('abc');

        $this->assertNotNull($confirmation);
        $this->assertSame(7, $confirmation->usersId);
        $this->assertFalse($confirmation->confirmed);
    }

    /**
     * Integration Tests Vokuro\Infrastructure\Repository\EmailConfirmationRepository :: findByCode misses as null
     */
    public function testFindByCodeMissingIsNull(): void
    {
        $this->assertNull($this->repository->findByCode('nope'));
    }

    /**
     * Integration Tests Vokuro\Infrastructure\Repository\EmailConfirmationRepository :: markConfirmed flips the flag
     */
    public function testMarkConfirmed(): void
    {
        $id = $this->insert('email_confirmations', [
            'usersId'   => 7,
            'code'      => 'abc',
            'createdAt' => 100,
            'confirmed' => 'N',
        ]);

        $this->repository->markConfirmed($id);

        $row = $this->connection->fetchOne('SELECT confirmed FROM email_confirmations WHERE id = ' . $id);
        $this->assertSame('Y', $row['confirmed']);
    }
}
