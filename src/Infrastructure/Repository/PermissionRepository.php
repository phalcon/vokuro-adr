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
use Vokuro\Contracts\Repository\PermissionRepository as PermissionRepositoryInterface;

/**
 * Profile permissions, over the data mapper.
 */
final class PermissionRepository implements PermissionRepositoryInterface
{
    public function __construct(
        private Connection $connection,
        private QueryFactory $queryFactory
    ) {
    }

    public function grantedTo(int $profileId): array
    {
        $select = $this->queryFactory->newSelect($this->connection);
        $select
            ->from('permissions')
            ->columns(['resource', 'action'])
            ->where('profilesId = ', $profileId);

        $rows    = $this->connection->fetchAll(
            $select->getStatement(),
            $select->getBindValues()
        );
        $granted = [];
        foreach ($rows as $row) {
            $granted[$row['resource'] . '.' . $row['action']] = true;
        }

        return $granted;
    }

    public function replaceForProfile(int $profileId, array $pairs): void
    {
        $delete = $this->queryFactory->newDelete($this->connection);
        $delete
            ->from('permissions')
            ->where('profilesId = ', $profileId);

        $delete->perform();

        foreach ($pairs as $pair) {
            if (!str_contains($pair, '.')) {
                continue;
            }

            [$resource, $action] = explode('.', $pair, 2);

            $insert = $this->queryFactory->newInsert($this->connection);
            $insert
                ->into('permissions')
                ->columns(
                    [
                        'profilesId' => $profileId,
                        'resource'   => $resource,
                        'action'     => $action,
                    ]
                );

            $insert->perform();
        }
    }
}
