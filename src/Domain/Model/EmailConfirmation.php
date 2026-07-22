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
 * A single use code that confirms an address belongs to the person who
 * registered it.
 */
final class EmailConfirmation
{
    public function __construct(
        public readonly int $id,
        public readonly int $usersId,
        public readonly bool $confirmed
    ) {
    }
}
