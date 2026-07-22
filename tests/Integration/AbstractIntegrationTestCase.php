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

namespace Vokuro\Tests\Integration;

use Phalcon\DataMapper\Pdo\Connection;
use Phalcon\DataMapper\Query\QueryFactory;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;

use function getenv;
use function sprintf;

/**
 * Base for integration tests: a real connection to the dedicated
 * `vokuro_adr_test` database, plus clean/seed helpers. Isolation is by
 * truncate-and-reseed per test - no transactions.
 */
abstract class AbstractIntegrationTestCase extends AbstractUnitTestCase
{
    protected Connection $connection;

    protected QueryFactory $queryFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = new Connection(
            sprintf(
                'mysql:host=%s;port=%d;dbname=vokuro_adr_test;charset=utf8mb4',
                getenv('DB_HOST') ?: 'mysql',
                (int) (getenv('DB_PORT') ?: 3306)
            ),
            getenv('DB_USERNAME') ?: 'root',
            getenv('DB_PASSWORD') ?: 'secret'
        );

        $this->queryFactory = new QueryFactory();
    }

    /**
     * Empties each table and resets its AUTO_INCREMENT, so inserted rows get
     * predictable ids. FK checks are toggled off defensively (the schema has no
     * FK constraints today, but this keeps the helper correct if any are added).
     */
    protected function clean(string ...$tables): void
    {
        $this->connection->exec('SET FOREIGN_KEY_CHECKS=0');

        foreach ($tables as $table) {
            $this->connection->exec('TRUNCATE TABLE ' . $table);
        }

        $this->connection->exec('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * Seeds one row and returns its id.
     *
     * @param array<string, mixed> $row
     */
    protected function insert(string $table, array $row): int
    {
        $insert = $this->queryFactory->newInsert($this->connection);
        $insert->into($table)->columns($row);
        $insert->perform();

        return (int) $insert->getLastInsertId();
    }
}
