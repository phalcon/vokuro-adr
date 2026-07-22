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

use Vokuro\Contracts\Repository\FailedLoginRepository;

/**
 * In-memory {@see FailedLoginRepository}. `$recent` drives the throttle.
 */
final class FakeFailedLoginRepository implements FailedLoginRepository
{
    public int $recent = 0;

    /** @var array<int, array{userId: int|null, ipAddress: string}> */
    public array $added = [];

    public function add(?int $userId, string $ipAddress): void
    {
        $this->added[] = ['userId' => $userId, 'ipAddress' => $ipAddress];
    }

    public function recentCount(string $ipAddress, int $since): int
    {
        return $this->recent;
    }
}
