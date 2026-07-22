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

use Vokuro\Contracts\Authorization;

/**
 * An {@see Authorization} that grants only the pairs it was seeded with.
 */
final class FakeAuthorization implements Authorization
{
    /** @var array<string, bool> */
    public array $granted = [];

    /** @var array<int, array{profileId: int, resource: string, action: string}> */
    public array $asked = [];

    public function allow(int $profileId, string $resource, string $action): self
    {
        $this->granted[$profileId . ':' . $resource . ':' . $action] = true;

        return $this;
    }

    public function isAllowed(int $profileId, string $resource, string $action): bool
    {
        $this->asked[] = ['profileId' => $profileId, 'resource' => $resource, 'action' => $action];

        return $this->granted[$profileId . ':' . $resource . ':' . $action] ?? false;
    }
}
