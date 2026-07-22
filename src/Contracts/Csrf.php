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

use Phalcon\Contracts\Http\AttributeRequest;

/**
 * Cross-site request forgery protection.
 *
 * `Phalcon\Encryption\Security` handles CSRF through a container an ADR
 * application does not have, so the mechanism lives behind this small port: a
 * form renders `token()`, and the action that receives the submission asks
 * `check()` whether the token that came back is the one that was issued. The
 * field name never leaves the adapter.
 */
interface Csrf
{
    /**
     * Validates the token carried by the request against the issued one.
     */
    public function check(AttributeRequest $request): bool;

    /**
     * The token value to render in a form.
     */
    public function token(): string;
}
