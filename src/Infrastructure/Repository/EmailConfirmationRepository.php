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
use Vokuro\Contracts\Repository\EmailConfirmationRepository as EmailConfirmationRepositoryInterface;
use Vokuro\Domain\Model\EmailConfirmation;

/**
 * Confirmation codes, over the data mapper.
 */
final class EmailConfirmationRepository implements EmailConfirmationRepositoryInterface
{
    public function __construct(
        private Connection $connection,
        private QueryFactory $queryFactory
    ) {
    }

    public function add(int $userId, string $code): string
    {
        $insert = $this->queryFactory->newInsert($this->connection);
        $insert
            ->into('email_confirmations')
            ->columns(
                [
                    'usersId'   => $userId,
                    'code'      => $code,
                    'createdAt' => time(),
                    'confirmed' => 'N',
                ]
            );

        $insert->perform();

        return $code;
    }

    public function findByCode(string $code): ?EmailConfirmation
    {
        $select = $this->queryFactory->newSelect($this->connection);
        $select
            ->from('email_confirmations')
            ->columns(['id', 'usersId', 'confirmed'])
            ->where('code = ', $code)
            ->limit(1);

        $row = $this->connection->fetchOne(
            $select->getStatement(),
            $select->getBindValues()
        );

        if ([] === $row) {
            return null;
        }

        return new EmailConfirmation(
            id: (int) $row['id'],
            usersId: (int) $row['usersId'],
            confirmed: 'Y' === $row['confirmed']
        );
    }

    public function markConfirmed(int $id): void
    {
        $update = $this->queryFactory->newUpdate($this->connection);
        $update
            ->from('email_confirmations')
            ->columns(['confirmed' => 'Y', 'modifiedAt' => time()])
            ->where('id = ', $id);

        $update->perform();
    }
}
