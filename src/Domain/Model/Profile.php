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
 * A profile groups permissions into a role that users are assigned to.
 */
final class Profile
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly bool $active
    ) {
    }
}
