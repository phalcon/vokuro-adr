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
use Vokuro\Contracts\Repository\RememberTokenRepository as RememberTokenRepositoryInterface;

/**
 * Remember-me tokens, over the data mapper.
 */
final class RememberTokenRepository implements RememberTokenRepositoryInterface
{
    public function __construct(
        private Connection $connection,
        private QueryFactory $queryFactory
    ) {
    }

    public function add(int $userId, string $tokenHash, string $userAgent): void
    {
        $insert = $this->queryFactory->newInsert($this->connection);
        $insert
            ->into('remember_tokens')
            ->columns(
                [
                    'usersId'   => $userId,
                    'token'     => $tokenHash,
                    'userAgent' => $userAgent,
                    'createdAt' => time(),
                ]
            );

        $insert->perform();
    }

    public function deleteForUser(int $userId): void
    {
        $delete = $this->queryFactory->newDelete($this->connection);
        $delete
            ->from('remember_tokens')
            ->where('usersId = ', $userId);

        $delete->perform();
    }

    public function findUserByToken(string $tokenHash): ?int
    {
        $select = $this->queryFactory->newSelect($this->connection);
        $select
            ->from('remember_tokens')
            ->columns(['usersId'])
            ->where('token = ', $tokenHash)
            ->limit(1);

        $value = $this->connection->fetchValue(
            $select->getStatement(),
            $select->getBindValues()
        );

        return false === $value ? null : (int) $value;
    }
}
