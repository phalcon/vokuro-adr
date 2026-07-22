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

namespace Vokuro\Action\Session;

use Phalcon\ADR\Payload\Payload;
use Phalcon\ADR\Responder\Redirect;
use Phalcon\ADR\Responder\RedirectResponder;
use Phalcon\Contracts\ADR\Action;
use Phalcon\Contracts\Http\AttributeRequest;
use Phalcon\Http\Response;
use Phalcon\Http\ResponseInterface;
use Phalcon\Session\ManagerInterface;
use Vokuro\Application\RememberMe;

/**
 * Signs a visitor out: the remember-me token is forgotten so the cookie cannot
 * sign them back in, then the session is dropped and they are sent home.
 */
final class GetSessionLogout implements Action
{
    public function __construct(
        private RedirectResponder $redirect,
        private ManagerInterface $session,
        private RememberMe $rememberMe
    ) {
    }

    public function __invoke(AttributeRequest $request): ResponseInterface
    {
        $auth = (array) $this->session->get('auth');

        if (isset($auth['id'])) {
            $this->rememberMe->forget((int) $auth['id']);
        }

        $this->session->destroy();

        return ($this->redirect)(
            $request,
            new Response(),
            Payload::found(new Redirect('/'))
        );
    }
}
