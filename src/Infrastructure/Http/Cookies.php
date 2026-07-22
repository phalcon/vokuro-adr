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

namespace Vokuro\Infrastructure\Http;

use Vokuro\Contracts\Cookies as CookiesInterface;

use function setcookie;

/**
 * Cookies over the PHP runtime.
 */
final class Cookies implements CookiesInterface
{
    public function delete(string $name): void
    {
        unset($_COOKIE[$name]);
        setcookie($name, '', ['expires' => 1, 'path' => '/', 'httponly' => true]);
    }

    public function get(string $name): ?string
    {
        return isset($_COOKIE[$name]) ? (string) $_COOKIE[$name] : null;
    }

    public function set(string $name, string $value, int $expires): void
    {
        $_COOKIE[$name] = $value;
        setcookie(
            $name,
            $value,
            ['expires' => $expires, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax']
        );
    }
}
