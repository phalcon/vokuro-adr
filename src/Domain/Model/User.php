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

namespace Vokuro\Domain\Model;

/**
 * A user of the application.
 *
 * The `Y`/`N` flags the database stores are exposed as booleans, so nothing
 * downstream compares against a single character. `profileName` is filled from
 * the joined profile and is empty when the row was read without it.
 */
final class User
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
        public readonly string $passwordHash,
        public readonly int $profileId,
        public readonly string $profileName,
        public readonly bool $active,
        public readonly bool $banned,
        public readonly bool $suspended,
        public readonly bool $mustChangePassword
    ) {
    }
}
