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

namespace Vokuro\Contracts\Repository;

use Vokuro\Domain\Collection\UserCollection;
use Vokuro\Domain\Model\User;
use Vokuro\Domain\Page;

/**
 * Reads and writes users.
 */
interface UserRepository
{
    /**
     * Stores a new user and returns its id.
     *
     * @param array<string, mixed> $user
     */
    public function add(array $user): int;

    /**
     * The user for an address, or null when there is no account for it.
     */
    public function findByEmail(string $email): ?User;

    /**
     * The users assigned to a profile.
     */
    public function byProfile(int $profileId): UserCollection;

    /**
     * The user with an id, or null when there is none.
     */
    public function findById(int $id): ?User;

    /**
     * A page of users, most recent first, narrowed by any of the filters.
     *
     * @param array<string, mixed> $filters id / name / email / profilesId
     */
    public function page(int $page, int $perPage, array $filters = []): Page;

    /**
     * Updates the mutable fields of a user.
     *
     * @param array<string, mixed> $fields
     */
    public function update(int $id, array $fields): void;

    /**
     * Removes a user.
     */
    public function delete(int $id): void;
}
