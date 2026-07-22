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
use Phalcon\DataMapper\Query\Select;
use Vokuro\Contracts\Repository\UserRepository as UserRepositoryInterface;
use Vokuro\Domain\Collection\UserCollection;
use Vokuro\Domain\Model\User;
use Vokuro\Domain\Page;

/**
 * Users, over the data mapper. `Mvc\Model` needs a `Phalcon\Di\DiInterface`,
 * which an ADR application does not have, and the data mapper needs no
 * container at all.
 */
final class UserRepository implements UserRepositoryInterface
{
    private const COLUMNS = [
        'u.id',
        'u.name',
        'u.email',
        'u.password',
        'u.profilesId',
        'p.name AS profileName',
        'u.active',
        'u.banned',
        'u.suspended',
        'u.mustChangePassword',
    ];

    public function __construct(
        private Connection $connection,
        private QueryFactory $queryFactory
    ) {
    }

    public function add(array $user): int
    {
        $insert = $this->queryFactory->newInsert($this->connection);
        $insert
            ->into('users')
            ->columns($user);

        $insert->perform();

        return (int) $insert->getLastInsertId();
    }

    public function findByEmail(string $email): ?User
    {
        $select = $this->baseSelect()->where('u.email = ', $email)->limit(1);

        $row = $this->connection->fetchOne(
            $select->getStatement(),
            $select->getBindValues()
        );

        return [] === $row ? null : $this->hydrate($row);
    }

    public function byProfile(int $profileId): UserCollection
    {
        $select = $this->baseSelect()
            ->where('u.profilesId = ', $profileId)
            ->orderBy(['u.id DESC']);

        $rows = $this->connection->fetchAll(
            $select->getStatement(),
            $select->getBindValues()
        );

        return new UserCollection(
            array_map(fn(array $row): User => $this->hydrate($row), $rows)
        );
    }

    public function delete(int $id): void
    {
        $delete = $this->queryFactory->newDelete($this->connection);
        $delete
            ->from('users')
            ->where('id = ', $id);

        $delete->perform();
    }

    public function findById(int $id): ?User
    {
        $select = $this->baseSelect()->where('u.id = ', $id)->limit(1);

        $row = $this->connection->fetchOne(
            $select->getStatement(),
            $select->getBindValues()
        );

        return [] === $row ? null : $this->hydrate($row);
    }

    public function update(int $id, array $fields): void
    {
        $update = $this->queryFactory->newUpdate($this->connection);
        $update
            ->from('users')
            ->columns($fields)
            ->where('id = ', $id);

        $update->perform();
    }

    public function page(int $page, int $perPage, array $filters = []): Page
    {
        $where = $this->filters($filters);

        $count = $this->queryFactory->newSelect($this->connection);
        $count->from('users u')->columns(['COUNT(*)']);
        $this->applyFilters($count, $where);

        $total = (int) $this->connection->fetchValue(
            $count->getStatement(),
            $count->getBindValues()
        );

        $last    = max(1, (int) ceil($total / $perPage));
        $current = max(1, min($page, $last));

        $select = $this->baseSelect();
        $this->applyFilters($select, $where);
        $select
            ->orderBy(['u.id DESC'])
            ->limit($perPage)
            ->offset(($current - 1) * $perPage);

        $rows  = $this->connection->fetchAll(
            $select->getStatement(),
            $select->getBindValues()
        );
        $users = array_map(fn(array $row): User => $this->hydrate($row), $rows);

        return new Page(new UserCollection($users), $current, $last, $total);
    }

    private function applyFilters(Select $select, array $where): void
    {
        foreach ($where as $condition => $value) {
            $select->where($condition, $value);
        }
    }

    private function baseSelect(): Select
    {
        $select = $this->queryFactory->newSelect($this->connection);
        $select
            ->from('users u')
            ->columns(self::COLUMNS)
            ->join('LEFT', 'profiles p', 'p.id = u.profilesId');

        return $select;
    }

    /**
     * @param array<string, mixed> $filters
     *
     * @return array<string, mixed> condition => bound value
     */
    private function filters(array $filters): array
    {
        $where = [];

        if (!empty($filters['id'])) {
            $where['u.id = '] = (int) $filters['id'];
        }

        if (!empty($filters['name'])) {
            $where['u.name LIKE '] = '%' . $filters['name'] . '%';
        }

        if (!empty($filters['email'])) {
            $where['u.email LIKE '] = '%' . $filters['email'] . '%';
        }

        if (!empty($filters['profilesId'])) {
            $where['u.profilesId = '] = (int) $filters['profilesId'];
        }

        return $where;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrate(array $row): User
    {
        return new User(
            id: (int) $row['id'],
            name: (string) $row['name'],
            email: (string) $row['email'],
            passwordHash: (string) $row['password'],
            profileId: (int) $row['profilesId'],
            profileName: (string) ($row['profileName'] ?? ''),
            active: 'Y' === $row['active'],
            banned: 'Y' === $row['banned'],
            suspended: 'Y' === $row['suspended'],
            mustChangePassword: 'Y' === $row['mustChangePassword']
        );
    }
}
