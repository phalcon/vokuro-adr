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
use Vokuro\Contracts\Repository\ProfileRepository as ProfileRepositoryInterface;
use Vokuro\Domain\Collection\ProfileCollection;
use Vokuro\Domain\Model\Profile;
use Vokuro\Domain\Page;

/**
 * Profiles, over the data mapper.
 */
final class ProfileRepository implements ProfileRepositoryInterface
{
    private const COLUMNS = ['id', 'name', 'active'];

    public function __construct(
        private Connection $connection,
        private QueryFactory $queryFactory
    ) {
    }

    public function add(array $profile): int
    {
        $insert = $this->queryFactory->newInsert($this->connection);
        $insert
            ->into('profiles')
            ->columns($profile);

        $insert->perform();

        return (int) $insert->getLastInsertId();
    }

    public function delete(int $id): void
    {
        $delete = $this->queryFactory->newDelete($this->connection);
        $delete
            ->from('profiles')
            ->where('id = ', $id);

        $delete->perform();
    }

    public function findById(int $id): ?Profile
    {
        $select = $this->queryFactory->newSelect($this->connection);
        $select
            ->from('profiles')
            ->columns(self::COLUMNS)
            ->where('id = ', $id)
            ->limit(1);

        $row = $this->connection->fetchOne(
            $select->getStatement(),
            $select->getBindValues()
        );

        return [] === $row ? null : $this->hydrate($row);
    }

    public function listForSelect(): array
    {
        $select = $this->queryFactory->newSelect($this->connection);
        $select
            ->from('profiles')
            ->columns(['id', 'name'])
            ->orderBy(['name']);

        return $this->connection->fetchPairs(
            $select->getStatement(),
            $select->getBindValues()
        );
    }

    /**
     * @param array<string, mixed> $filters
     *
     * @return Page<Profile>
     */
    public function page(int $page, int $perPage, array $filters = []): Page
    {
        $where = $this->filters($filters);

        $count = $this->queryFactory->newSelect($this->connection);
        $count->from('profiles')->columns(['COUNT(*)']);
        $this->applyFilters($count, $where);

        $total = (int) $this->connection->fetchValue(
            $count->getStatement(),
            $count->getBindValues()
        );

        $last    = max(1, (int) ceil($total / $perPage));
        $current = max(1, min($page, $last));

        $select = $this->queryFactory->newSelect($this->connection);
        $select->from('profiles')->columns(self::COLUMNS);
        $this->applyFilters($select, $where);
        $select
            ->orderBy(['id DESC'])
            ->limit($perPage)
            ->offset(($current - 1) * $perPage);

        $rows     = $this->connection->fetchAll(
            $select->getStatement(),
            $select->getBindValues()
        );
        $profiles = array_map(fn(array $row): Profile => $this->hydrate($row), $rows);

        return new Page(new ProfileCollection($profiles), $current, $last, $total);
    }

    public function update(int $id, array $fields): void
    {
        $update = $this->queryFactory->newUpdate($this->connection);
        $update
            ->from('profiles')
            ->columns($fields)
            ->where('id = ', $id);

        $update->perform();
    }

    /**
     * @param array<string, mixed> $where
     */
    private function applyFilters(Select $select, array $where): void
    {
        foreach ($where as $condition => $value) {
            $select->where($condition, $value);
        }
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
            $where['id = '] = (int) $filters['id'];
        }

        if (!empty($filters['name'])) {
            $where['name LIKE '] = '%' . $filters['name'] . '%';
        }

        return $where;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrate(array $row): Profile
    {
        return new Profile(
            id: (int) $row['id'],
            name: (string) $row['name'],
            active: 'Y' === $row['active']
        );
    }
}
