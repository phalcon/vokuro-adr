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

namespace Vokuro\Action\Confirm;

use Phalcon\ADR\Input\Input;
use Phalcon\ADR\Payload\Payload;
use Phalcon\ADR\Payload\Status;
use Phalcon\ADR\Responder\Redirect;
use Phalcon\ADR\Responder\RedirectResponder;
use Phalcon\Contracts\ADR\Action;
use Phalcon\Contracts\Http\AttributeRequest;
use Phalcon\Http\Response;
use Phalcon\Http\ResponseInterface;
use Phalcon\Session\ManagerInterface;
use Vokuro\Domain\Confirm\ConfirmEmail;

/**
 * Handles the confirmation link from the e-mail. The code is the first path
 * attribute. A confirmed user is signed in and sent on to change their
 * password when they still owe one, or to the management area otherwise.
 */
final class GetConfirm implements Action
{
    public function __construct(
        private ConfirmEmail $domain,
        private RedirectResponder $redirect,
        private ManagerInterface $session
    ) {
    }

    public function __invoke(AttributeRequest $request): ResponseInterface
    {
        $code    = (string) $request->getAttributes()->get(0);
        $payload = ($this->domain)(Input::fromArray(['code' => $code]));

        $to = match ($payload->getStatus()) {
            Status::UPDATED   => $this->signIn((array) $payload->getResult()),
            Status::NOT_VALID => '/session/login',
            default           => '/',
        };

        return ($this->redirect)(
            $request,
            new Response(),
            Payload::found(new Redirect($to))
        );
    }

    /**
     * @param array<string, mixed> $user
     */
    private function signIn(array $user): string
    {
        $this->session->set(
            'auth',
            [
                'id'         => $user['id'],
                'name'       => $user['name'],
                'email'      => $user['email'],
                'profilesId' => $user['profilesId'],
            ]
        );

        return true === $user['mustChangePassword']
            ? '/users/changePassword'
            : '/users';
    }
}
