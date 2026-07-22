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

use Vokuro\Domain\Collection\ResetPasswordCollection;

/**
 * Stores the single use codes that let a user set a new password.
 */
interface ResetPasswordRepository
{
    /**
     * Records a code against a user and returns it.
     */
    public function add(int $userId, string $code): string;

    /**
     * The reset requests made for a user, most recent first.
     */
    public function forUser(int $userId): ResetPasswordCollection;
}
