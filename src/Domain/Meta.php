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

namespace Vokuro\Domain;

/**
 * Application state that a template consumes, carried on the payload's extras.
 *
 * It holds facts, never presentation decisions - no labels, classes or menus.
 * Every property defaults to its least privileged value, so a payload that
 * never populates it renders as if nothing had been granted.
 */
final class Meta
{
    public function __construct(
        public readonly bool $isLoggedIn = false,
        public readonly string $name = ''
    ) {
    }
}
