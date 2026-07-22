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

use Phalcon\ADR\Input\Input;
use Phalcon\ADR\Payload\Payload;
use Phalcon\ADR\Payload\Status;
use Phalcon\ADR\Responder\Redirect;
use Phalcon\ADR\Responder\RedirectResponder;
use Phalcon\Contracts\ADR\Action;
use Phalcon\Contracts\Http\AttributeRequest;
use Phalcon\Encryption\Security;
use Phalcon\Http\Response;
use Phalcon\Http\ResponseInterface;
use Phalcon\Session\ManagerInterface;
use Vokuro\Application\RememberMe;
use Vokuro\Domain\Session\Login;
use Vokuro\Responder\AuthResponder;

/**
 * Signs a visitor in.
 *
 * The outcome decides the shape of the answer, so the action holds both
 * responders: a success redirects, anything else renders the form again with
 * the messages the domain produced.
 */
final class PostSessionLogin implements Action
{
    public function __construct(
        private Login $domain,
        private AuthResponder $view,
        private RedirectResponder $redirect,
        private ManagerInterface $session,
        private Security $security,
        private RememberMe $rememberMe
    ) {
    }

    public function __invoke(AttributeRequest $request): ResponseInterface
    {
        /**
         * The token is read here and handed over. Letting `Security` fetch it
         * applies a sanitizer, and sanitizers need a container the request
         * cannot be given, while asking for the key would mint a new one and
         * invalidate the token that was just rendered.
         */
        if (false === $this->security->checkToken('csrf', $request->getPost('csrf'))) {
            return $this->form(
                $request,
                Payload::invalid(['The form has expired, please try again'])
            );
        }

        $payload = ($this->domain)(
            new Input(
                [
                    'ipAddress' => (string) $request->getClientAddress(),
                    'userAgent' => (string) $request->getUserAgent(),
                ] + $request->getPost()
            )
        );

        if (Status::AUTHENTICATED !== $payload->getStatus()) {
            return $this->form($request, $payload);
        }

        /** @var array<string, mixed> $auth */
        $auth = (array) $payload->getResult();
        $this->session->set('auth', $auth);

        if (null !== $request->getPost('remember')) {
            $this->rememberMe->remember(
                (int) $auth['id'],
                (string) $request->getUserAgent()
            );
        }

        return ($this->redirect)(
            $request,
            new Response(),
            Payload::found(new Redirect('/'))
        );
    }

    private function form(
        AttributeRequest $request,
        Payload $payload
    ): ResponseInterface {
        return ($this->view->withTemplate('session/login'))(
            $request,
            new Response(),
            $payload
        );
    }
}
