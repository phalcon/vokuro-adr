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

namespace Vokuro\Middleware;

use Phalcon\Contracts\ADR\Handler;
use Phalcon\Contracts\ADR\Middleware;
use Phalcon\Contracts\Http\AttributeRequest;
use Phalcon\Http\ResponseInterface;
use Phalcon\Session\ManagerInterface;
use Vokuro\Application\RememberMe;

/**
 * Restores a session from the remember-me cookie before anything else runs, so
 * a returning visitor is signed in without seeing the login form. It is global:
 * every request gets the chance, not only the guarded ones.
 */
final class RememberMeLogin implements Middleware
{
    public function __construct(
        private ManagerInterface $session,
        private RememberMe $rememberMe
    ) {
    }

    public function __invoke(AttributeRequest $request, Handler $next): ResponseInterface
    {
        if (false === $this->session->has('auth')) {
            $auth = $this->rememberMe->recall();

            if (null !== $auth) {
                $this->session->set('auth', $auth);
            }
        }

        return $next($request);
    }
}
