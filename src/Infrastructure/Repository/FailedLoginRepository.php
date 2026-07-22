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

namespace Vokuro\Infrastructure\Repository;

use Phalcon\DataMapper\Pdo\Connection;
use Phalcon\DataMapper\Query\QueryFactory;
use Vokuro\Contracts\Repository\FailedLoginRepository as FailedLoginRepositoryInterface;

/**
 * Failed sign in attempts, over the data mapper.
 */
final class FailedLoginRepository implements FailedLoginRepositoryInterface
{
    public function __construct(
        private Connection $connection,
        private QueryFactory $queryFactory
    ) {
    }

    public function add(?int $userId, string $ipAddress): void
    {
        $insert = $this->queryFactory->newInsert($this->connection);
        $insert
            ->into('failed_logins')
            ->columns(
                [
                    'usersId'   => $userId,
                    'ipAddress' => $ipAddress,
                    'attempted' => time(),
                ]
            );

        $insert->perform();
    }

    public function recentCount(string $ipAddress, int $since): int
    {
        $select = $this->queryFactory->newSelect($this->connection);
        $select
            ->from('failed_logins')
            ->columns(['COUNT(*)'])
            ->where('ipAddress = ', $ipAddress)
            ->where('attempted >= ', $since);

        return (int) $this->connection->fetchValue(
            $select->getStatement(),
            $select->getBindValues()
        );
    }
}
