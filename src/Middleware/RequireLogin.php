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
use Phalcon\Http\Response;
use Phalcon\Http\ResponseInterface;
use Phalcon\Session\ManagerInterface;

/**
 * Guards the pages that need an account.
 *
 * The router attaches it to every action under a namespace, so a whole area is
 * protected by one line of configuration rather than a check in each action.
 * A visitor without a session is sent to the login form.
 */
final class RequireLogin implements Middleware
{
    public function __construct(
        private ManagerInterface $session
    ) {
    }

    public function __invoke(AttributeRequest $request, Handler $next): ResponseInterface
    {
        if (false === $this->session->has('auth')) {
            return (new Response())
                ->setStatusCode(302)
                ->setHeader('Location', '/session/login');
        }

        return $next($request);
    }
}
