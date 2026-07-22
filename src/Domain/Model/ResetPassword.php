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
 * A record of a password reset request.
 */
final class ResetPassword
{
    public function __construct(
        public readonly int $id,
        public readonly int $createdAt,
        public readonly bool $reset
    ) {
    }
}
