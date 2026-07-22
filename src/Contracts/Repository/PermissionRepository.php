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

/**
 * Reads and writes the permissions a profile holds.
 */
interface PermissionRepository
{
    /**
     * The permissions a profile holds, as a `resource.action => true` lookup
     * so the screen can test each checkbox in one step.
     *
     * @return array<string, bool>
     */
    public function grantedTo(int $profileId): array;

    /**
     * Replaces the profile's permissions with the given `resource.action`
     * pairs, so a save is the whole set at once.
     *
     * @param list<string> $pairs
     */
    public function replaceForProfile(int $profileId, array $pairs): void;
}
