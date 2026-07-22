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

namespace Vokuro\Tests\Support\Fake;

use Vokuro\Contracts\Cookies;

/**
 * In-memory {@see Cookies}.
 */
final class FakeCookies implements Cookies
{
    /** @var array<string, string> */
    public array $jar = [];

    /** @var array<int, string> */
    public array $deleted = [];

    public function seed(string $name, string $value): self
    {
        $this->jar[$name] = $value;

        return $this;
    }

    public function delete(string $name): void
    {
        $this->deleted[] = $name;
        unset($this->jar[$name]);
    }

    public function get(string $name): ?string
    {
        return $this->jar[$name] ?? null;
    }

    public function set(string $name, string $value, int $expires): void
    {
        $this->jar[$name] = $value;
    }
}
