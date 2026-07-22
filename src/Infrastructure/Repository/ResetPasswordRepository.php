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
use Vokuro\Contracts\Repository\ResetPasswordRepository as ResetPasswordRepositoryInterface;
use Vokuro\Domain\Collection\ResetPasswordCollection;
use Vokuro\Domain\Model\ResetPassword;

/**
 * Reset codes, over the data mapper.
 */
final class ResetPasswordRepository implements ResetPasswordRepositoryInterface
{
    public function __construct(
        private Connection $connection,
        private QueryFactory $queryFactory
    ) {
    }

    public function add(int $userId, string $code): string
    {
        $now    = time();
        $insert = $this->queryFactory->newInsert($this->connection);
        $insert
            ->into('reset_passwords')
            ->columns(
                [
                    'usersId'    => $userId,
                    'code'       => $code,
                    'createdAt'  => $now,
                    'modifiedAt' => $now,
                    'reset'      => 'N',
                ]
            );

        $insert->perform();

        return $code;
    }

    public function forUser(int $userId): ResetPasswordCollection
    {
        $select = $this->queryFactory->newSelect($this->connection);
        $select
            ->from('reset_passwords')
            ->columns(['id', 'createdAt', 'reset'])
            ->where('usersId = ', $userId)
            ->orderBy(['id DESC']);

        $rows = $this->connection->fetchAll(
            $select->getStatement(),
            $select->getBindValues()
        );

        return new ResetPasswordCollection(
            array_map(
                fn(array $row): ResetPassword => new ResetPassword(
                    id: (int) $row['id'],
                    createdAt: (int) $row['createdAt'],
                    reset: 'Y' === $row['reset']
                ),
                $rows
            )
        );
    }
}
