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

namespace Vokuro\Tests\Support\Fake;

use Vokuro\Contracts\Repository\UserRepository;
use Vokuro\Domain\Collection\UserCollection;
use Vokuro\Domain\Model\User;
use Vokuro\Domain\Page;

/**
 * In-memory {@see UserRepository}. Records writes so a test can assert on them.
 */
final class FakeUserRepository implements UserRepository
{
    /** @var array<int, User> */
    public array $users = [];

    /** @var array<int, array<string, mixed>> */
    public array $added = [];

    /** @var array<int, array<string, mixed>> */
    public array $updated = [];

    /** @var array<int, int> */
    public array $deleted = [];

    private int $nextId = 1;

    public function seed(User $user): self
    {
        $this->users[$user->id] = $user;
        $this->nextId = max($this->nextId, $user->id + 1);

        return $this;
    }

    public function add(array $user): int
    {
        $id                = $this->nextId++;
        $this->added[$id]  = $user;
        $this->users[$id]  = new User(
            $id,
            (string) ($user['name'] ?? ''),
            (string) ($user['email'] ?? ''),
            (string) ($user['password'] ?? ''),
            (int) ($user['profilesId'] ?? 0),
            '',
            'Y' === ($user['active'] ?? 'N'),
            'Y' === ($user['banned'] ?? 'N'),
            'Y' === ($user['suspended'] ?? 'N'),
            'Y' === ($user['mustChangePassword'] ?? 'N')
        );

        return $id;
    }

    public function byProfile(int $profileId): UserCollection
    {
        return new UserCollection(
            array_values(array_filter($this->users, fn(User $u): bool => $u->profileId === $profileId))
        );
    }

    public function delete(int $id): void
    {
        $this->deleted[] = $id;
        unset($this->users[$id]);
    }

    public function findByEmail(string $email): ?User
    {
        foreach ($this->users as $user) {
            if ($user->email === $email) {
                return $user;
            }
        }

        return null;
    }

    public function findById(int $id): ?User
    {
        return $this->users[$id] ?? null;
    }

    public function page(int $page, int $perPage, array $filters = []): Page
    {
        return new Page(new UserCollection(array_values($this->users)), $page, $page, count($this->users));
    }

    public function update(int $id, array $fields): void
    {
        $this->updated[$id] = $fields;
    }
}
