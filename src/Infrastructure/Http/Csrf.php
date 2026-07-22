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

use Phalcon\Contracts\Http\AttributeRequest;
use Phalcon\Encryption\Security;
use Vokuro\Contracts\Csrf as CsrfInterface;

/**
 * CSRF over `Phalcon\Encryption\Security`.
 *
 * The security service is constructed with the session and request, so its
 * token generation and constant-time comparison run without a container. Only
 * two things here avoid the container the ADR app cannot provide: the token is
 * rendered under the fixed field name `csrf` rather than the random key, and the
 * posted value is read and handed over so `Security` never applies its filter.
 */
final class Csrf implements CsrfInterface
{
    private const FIELD = 'csrf';

    public function __construct(
        private Security $security
    ) {
    }

    public function check(AttributeRequest $request): bool
    {
        return $this->security->checkToken(
            self::FIELD,
            $request->getPost(self::FIELD)
        );
    }

    public function token(): string
    {
        return (string) $this->security->getToken();
    }
}
