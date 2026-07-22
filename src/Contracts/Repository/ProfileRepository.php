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

use Vokuro\Domain\Model\Profile;
use Vokuro\Domain\Page;

/**
 * Reads and writes profiles.
 */
interface ProfileRepository
{
    /**
     * Stores a new profile and returns its id.
     *
     * @param array<string, mixed> $profile
     */
    public function add(array $profile): int;

    /**
     * Removes a profile.
     */
    public function delete(int $id): void;

    /**
     * The profile with an id, or null when there is none.
     */
    public function findById(int $id): ?Profile;

    /**
     * @return array<int, string> id => name, for a select element
     */
    public function listForSelect(): array;

    /**
     * A page of profiles, most recent first, narrowed by any of the filters.
     *
     * @param array<string, mixed> $filters id / name
     */
    public function page(int $page, int $perPage, array $filters = []): Page;

    /**
     * Updates a profile.
     *
     * @param array<string, mixed> $fields
     */
    public function update(int $id, array $fields): void;
}
