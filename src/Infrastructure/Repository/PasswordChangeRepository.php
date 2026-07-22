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
use Vokuro\Contracts\Repository\PasswordChangeRepository as PasswordChangeRepositoryInterface;
use Vokuro\Domain\Collection\PasswordChangeCollection;
use Vokuro\Domain\Model\PasswordChange;

/**
 * Password change records, over the data mapper.
 */
final class PasswordChangeRepository implements PasswordChangeRepositoryInterface
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
            ->into('password_changes')
            ->columns(
                [
                    'usersId'   => $userId,
                    'ipAddress' => $ipAddress,
                    'userAgent' => $userAgent,
                    'createdAt' => time(),
                ]
            );

        $insert->perform();
    }

    public function forUser(int $userId): PasswordChangeCollection
    {
        $select = $this->queryFactory->newSelect($this->connection);
        $select
            ->from('password_changes')
            ->columns(['id', 'ipAddress', 'userAgent', 'createdAt'])
            ->where('usersId = ', $userId)
            ->orderBy(['id DESC']);

        $rows = $this->connection->fetchAll(
            $select->getStatement(),
            $select->getBindValues()
        );

        return new PasswordChangeCollection(
            array_map(
                fn(array $row): PasswordChange => new PasswordChange(
                    id: (int) $row['id'],
                    ipAddress: (string) $row['ipAddress'],
                    userAgent: (string) $row['userAgent'],
                    createdAt: (int) $row['createdAt']
                ),
                $rows
            )
        );
    }
}
