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
use Vokuro\Contracts\Repository\SuccessLoginRepository as SuccessLoginRepositoryInterface;
use Vokuro\Domain\Collection\SuccessLoginCollection;
use Vokuro\Domain\Model\SuccessLogin;

/**
 * Successful sign in records, over the data mapper.
 */
final class SuccessLoginRepository implements SuccessLoginRepositoryInterface
{
    public function __construct(
        private Connection $connection,
        private QueryFactory $queryFactory
    ) {
    }

    public function add(int $userId, string $ipAddress, string $userAgent): void
    {
        $insert = $this->queryFactory->newInsert($this->connection);
        $insert
            ->into('success_logins')
            ->columns(
                [
                    'usersId'   => $userId,
                    'ipAddress' => $ipAddress,
                    'userAgent' => $userAgent,
                ]
            );

        $insert->perform();
    }

    public function forUser(int $userId): SuccessLoginCollection
    {
        $select = $this->queryFactory->newSelect($this->connection);
        $select
            ->from('success_logins')
            ->columns(['id', 'ipAddress', 'userAgent'])
            ->where('usersId = ', $userId)
            ->orderBy(['id DESC']);

        $rows = $this->connection->fetchAll(
            $select->getStatement(),
            $select->getBindValues()
        );

        return new SuccessLoginCollection(
            array_map(
                fn(array $row): SuccessLogin => new SuccessLogin(
                    id: (int) $row['id'],
                    ipAddress: (string) $row['ipAddress'],
                    userAgent: (string) $row['userAgent']
                ),
                $rows
            )
        );
    }
}
