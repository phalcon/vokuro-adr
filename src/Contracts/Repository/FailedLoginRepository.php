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
 * Records failed sign in attempts and counts recent ones, so repeated failures
 * from one address can be slowed down.
 */
interface FailedLoginRepository
{
    /**
     * Records a failed attempt. The user is null when the address is unknown.
     */
    public function add(?int $userId, string $ipAddress): void;

    /**
     * How many failed attempts an address has made since a point in time.
     */
    public function recentCount(string $ipAddress, int $since): int;
}
