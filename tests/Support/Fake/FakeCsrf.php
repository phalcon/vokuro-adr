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

use Phalcon\Contracts\Http\AttributeRequest;
use Vokuro\Contracts\Csrf;

/**
 * A {@see Csrf} whose verdict is fixed by the test. Records the checks so a test
 * can assert the token was verified.
 */
final class FakeCsrf implements Csrf
{
    public int $checks = 0;

    public function __construct(
        private bool $valid = true,
        private string $token = 'token'
    ) {
    }

    public function check(AttributeRequest $request): bool
    {
        $this->checks++;

        return $this->valid;
    }

    public function token(): string
    {
        return $this->token;
    }
}
