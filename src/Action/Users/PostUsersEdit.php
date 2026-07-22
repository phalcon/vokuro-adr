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
use Vokuro\Contracts\Repository\ProfileRepository;
use Vokuro\Contracts\Repository\UserRepository;
use Vokuro\Domain\Users\UpdateUser;
use Vokuro\Responder\PrivateResponder;

/**
 * Saves an edited user. A success returns to the list; a rejection renders the
 * form again with the current values and the per-field errors.
 */
final class PostUsersEdit implements Action
{
    private const ENDPOINT = '/users';

    public function __construct(
        private UpdateUser $domain,
        private UserRepository $users,
        private ProfileRepository $profiles,
        private PrivateResponder $view,
        private RedirectResponder $redirect,
        private Security $security
    ) {
    }

    public function __invoke(AttributeRequest $request): ResponseInterface
    {
        $id = (int) $request->getAttributes()->get(0);

        if (false === $this->security->checkToken('csrf', $request->getPost('csrf'))) {
            return $this->form(
                $request,
                $id,
                Payload::invalid(['csrf' => 'The form has expired, please try again'])
            );
        }

        $input   = new Input(
            ['id' => $id] + $request->getPost() + $request->getQuery()
        );
        $payload = ($this->domain)($input);

        if (Status::UPDATED === $payload->getStatus()) {
            return ($this->redirect)(
                $request,
                new Response(),
                Payload::found(new Redirect(self::ENDPOINT))
            );
        }

        if (Status::NOT_FOUND === $payload->getStatus()) {
            return ($this->redirect)(
                $request,
                new Response(),
                Payload::found(new Redirect(self::ENDPOINT))
            );
        }

        return $this->form($request, $id, $payload);
    }

    private function form(
        AttributeRequest $request,
        int $id,
        PayloadInterface $payload
    ): ResponseInterface {
        $user = $this->users->findById($id);

        if (null === $user) {
            return ($this->redirect)(
                $request,
                new Response(),
                Payload::found(new Redirect(self::ENDPOINT))
            );
        }

        return ($this->view->withTemplate('users/edit'))(
            $request,
            new Response(),
            $payload->withResult([
                'user'     => $user,
                'profiles' => $this->profiles->listForSelect(),
            ])
        );
    }
}
