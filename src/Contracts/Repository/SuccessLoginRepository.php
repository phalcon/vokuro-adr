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

use Vokuro\Domain\Collection\SuccessLoginCollection;

/**
 * Reads the successful sign in records.
 */
interface SuccessLoginRepository
{
    /**
     * Records a successful sign in.
     */
    public function add(int $userId, string $ipAddress, string $userAgent): void;

    public function forUser(int $userId): SuccessLoginCollection;
}
