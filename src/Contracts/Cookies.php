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

namespace Vokuro\Contracts;

/**
 * Reads and writes cookies.
 *
 * `Phalcon\Http\Response\Cookies` encrypts through the container, which an ADR
 * application does not have, so cookies are handled behind this small port and
 * the reminder token that rides in them is stored hashed, never in the clear.
 */
interface Cookies
{
    public function delete(string $name): void;

    public function get(string $name): ?string;

    public function set(string $name, string $value, int $expires): void;
}
