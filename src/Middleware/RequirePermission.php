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
use Vokuro\Contracts\Authorization;

use function explode;
use function trim;

/**
 * Checks the signed in user's profile against the resource being requested.
 *
 * The path is the permission: `/users/delete/3` needs `users.delete`, `/users`
 * needs `users.index`. Mapping the request to a permission is the middleware's
 * job; deciding the answer is left to the `Authorization` service. It runs
 * after `RequireLogin`, so a session is assured; a denied request is sent home.
 */
final class RequirePermission implements Middleware
{
    public function __construct(
        private ManagerInterface $session,
        private Authorization $authorization
    ) {
    }

    public function __invoke(AttributeRequest $request, Handler $next): ResponseInterface
    {
        $auth      = (array) $this->session->get('auth');
        $profileId = (int) ($auth['profilesId'] ?? 0);

        [$resource, $action] = $this->target($request);

        if (false === $this->authorization->isAllowed($profileId, $resource, $action)) {
            return (new Response())
                ->setStatusCode(302)
                ->setHeader('Location', '/');
        }

        return $next($request);
    }

    /**
     * The resource and action the path asks for: the first segment names the
     * resource, the second the action, defaulting to `index`.
     *
     * @return array{0: string, 1: string}
     */
    private function target(AttributeRequest $request): array
    {
        $uri      = trim($request->getURI(true), '/');
        $segments = '' === $uri ? [] : explode('/', $uri);

        return [$segments[0] ?? '', $segments[1] ?? 'index'];
    }
}
