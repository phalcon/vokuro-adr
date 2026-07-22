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
 * Stores the hashed tokens that let a returning visitor be recognised without
 * signing in again.
 */
interface RememberTokenRepository
{
    /**
     * Records a token against a user.
     */
    public function add(int $userId, string $tokenHash, string $userAgent): void;

    /**
     * Forgets every token a user holds.
     */
    public function deleteForUser(int $userId): void;

    /**
     * The user a token belongs to, or null when it is unknown.
     */
    public function findUserByToken(string $tokenHash): ?int;
}
