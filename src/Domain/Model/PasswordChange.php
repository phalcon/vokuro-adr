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
 * A record of a password change.
 */
final class PasswordChange
{
    public function __construct(
        public readonly int $id,
        public readonly string $ipAddress,
        public readonly string $userAgent,
        public readonly int $createdAt
    ) {
    }
}
