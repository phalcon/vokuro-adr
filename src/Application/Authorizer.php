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

namespace Vokuro\Application;

use Vokuro\Contracts\Authorization;
use Vokuro\Contracts\Repository\PermissionRepository;

/**
 * The authorization decision, backed by the permissions a profile holds.
 *
 * A profile's grants are read once and kept for the request, so a page that
 * asks about many controls costs a single query. Swapping the storage - a
 * different table, a cache, an external policy engine - changes only this
 * class; the callers keep asking `isAllowed()`.
 */
final class Authorizer implements Authorization
{
    /**
     * @var array<int, array<string, bool>>
     */
    private array $grants = [];

    public function __construct(
        private PermissionRepository $permissions
    ) {
    }

    public function isAllowed(int $profileId, string $resource, string $action): bool
    {
        $granted = $this->grants[$profileId] ??= $this->permissions->grantedTo($profileId);

        return isset($granted[$resource . '.' . $action]);
    }
}
