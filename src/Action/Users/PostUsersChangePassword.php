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

namespace Vokuro\Action\Users;

use Phalcon\ADR\Input\Input;
use Phalcon\ADR\Payload\Payload;
use Phalcon\ADR\Payload\Status;
use Phalcon\ADR\Responder\Redirect;
use Phalcon\ADR\Responder\RedirectResponder;
use Phalcon\Contracts\ADR\Action;
use Phalcon\Contracts\ADR\Payload\Payload as PayloadInterface;
use Phalcon\Contracts\Http\AttributeRequest;
use Phalcon\Encryption\Security;
use Phalcon\Http\Response;
use Phalcon\Http\ResponseInterface;
use Phalcon\Session\ManagerInterface;
use Vokuro\Domain\Users\ChangePassword;
use Vokuro\Responder\PrivateResponder;

/**
 * Changes the signed in user's password. The user is taken from the session,
 * never the form, so no one can change another account's password.
 */
final class PostUsersChangePassword implements Action
{
    public function __construct(
        private ChangePassword $domain,
        private PrivateResponder $view,
        private RedirectResponder $redirect,
        private Security $security,
        private ManagerInterface $session
    ) {
    }

    public function __invoke(AttributeRequest $request): ResponseInterface
    {
        if (false === $this->security->checkToken('csrf', $request->getPost('csrf'))) {
            return $this->form($request, Payload::invalid(['csrf' => 'The form has expired, please try again']));
        }

        $auth    = (array) $this->session->get('auth');
        $payload = ($this->domain)(
            new Input(
                [
                    'userId'    => $auth['id'] ?? 0,
                    'ipAddress' => (string) $request->getClientAddress(),
                    'userAgent' => (string) $request->getUserAgent(),
                ] + $request->getPost()
            )
        );

        if (Status::UPDATED === $payload->getStatus()) {
            return ($this->redirect)(
                $request,
                new Response(),
                Payload::found(new Redirect('/users'))
            );
        }

        return $this->form($request, $payload);
    }

    private function form(
        AttributeRequest $request,
        PayloadInterface $payload
    ): ResponseInterface {
        return ($this->view->withTemplate('users/changePassword'))(
            $request,
            new Response(),
            $payload
        );
    }
}
