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
use Phalcon\Http\Response;
use Phalcon\Http\ResponseInterface;
use Vokuro\Contracts\Csrf;
use Vokuro\Contracts\Repository\ProfileRepository;
use Vokuro\Domain\Users\CreateUser;
use Vokuro\Responder\PrivateResponder;

/**
 * Creates a user. A success redirects to the list; a rejection renders the
 * form again with the per-field errors.
 */
final class PostUsersCreate implements Action
{
    public function __construct(
        private CreateUser $domain,
        private ProfileRepository $profiles,
        private PrivateResponder $view,
        private RedirectResponder $redirect,
        private Csrf $csrf
    ) {
    }

    public function __invoke(AttributeRequest $request): ResponseInterface
    {
        if (false === $this->csrf->check($request)) {
            return $this->form(
                $request,
                Payload::invalid(['csrf' => 'The form has expired, please try again'])
            );
        }

        $payload = ($this->domain)(Input::fromRequest($request));

        if (Status::CREATED !== $payload->getStatus()) {
            return $this->form($request, $payload);
        }

        return ($this->redirect)(
            $request,
            new Response(),
            Payload::found(new Redirect('/users'))
        );
    }

    private function form(
        AttributeRequest $request,
        PayloadInterface $payload
    ): ResponseInterface {
        $payload = $payload->withResult(
            ['profiles' => $this->profiles->listForSelect()]
            + (array) $payload->getResult()
        );

        return ($this->view->withTemplate('users/create'))(
            $request,
            new Response(),
            $payload
        );
    }
}
