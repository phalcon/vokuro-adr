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

use Vokuro\Contracts\Repository\PermissionRepository;

/**
 * In-memory {@see PermissionRepository}. Grants are keyed `resource.action`.
 */
final class FakePermissionRepository implements PermissionRepository
{
    /** @var array<int, array<string, bool>> */
    public array $grants = [];

    /** @var array<int, int> */
    public array $grantedCalls = [];

    /** @var array<int, array{profileId: int, pairs: array<int, string>}> */
    public array $replaced = [];

    /**
     * @param array<int, string> $pairs
     */
    public function grant(int $profileId, array $pairs): self
    {
        foreach ($pairs as $pair) {
            $this->grants[$profileId][$pair] = true;
        }

        return $this;
    }

    public function grantedTo(int $profileId): array
    {
        $this->grantedCalls[] = $profileId;

        return $this->grants[$profileId] ?? [];
    }

    public function replaceForProfile(int $profileId, array $pairs): void
    {
        $this->replaced[] = ['profileId' => $profileId, 'pairs' => $pairs];
        $this->grants[$profileId] = [];
        foreach ($pairs as $pair) {
            $this->grants[$profileId][$pair] = true;
        }
    }
}
