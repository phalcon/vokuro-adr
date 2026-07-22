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

use Phalcon\ADR\Payload\Payload;
use Phalcon\ADR\Responder\Redirect;
use Phalcon\ADR\Responder\RedirectResponder;
use Phalcon\Contracts\ADR\Action;
use Phalcon\Contracts\Http\AttributeRequest;
use Phalcon\Http\Response;
use Phalcon\Http\ResponseInterface;
use Vokuro\Contracts\Repository\PasswordChangeRepository;
use Vokuro\Contracts\Repository\ProfileRepository;
use Vokuro\Contracts\Repository\ResetPasswordRepository;
use Vokuro\Contracts\Repository\SuccessLoginRepository;
use Vokuro\Contracts\Repository\UserRepository;
use Vokuro\Responder\PrivateResponder;

/**
 * Shows the edit form for a user. The id is the first path attribute, so
 * `/users/edit/3` edits user 3. A missing user returns to the list.
 */
final class GetUsersEdit implements Action
{
    public function __construct(
        private UserRepository $users,
        private ProfileRepository $profiles,
        private SuccessLoginRepository $logins,
        private PasswordChangeRepository $passwordChanges,
        private ResetPasswordRepository $resets,
        private PrivateResponder $view,
        private RedirectResponder $redirect
    ) {
    }

    public function __invoke(AttributeRequest $request): ResponseInterface
    {
        $user = $this->users->findById((int) $request->getAttributes()->get(0));

        if (null === $user) {
            return ($this->redirect)(
                $request,
                new Response(),
                Payload::found(new Redirect('/users'))
            );
        }

        return ($this->view->withTemplate('users/edit'))(
            $request,
            new Response(),
            Payload::success([
                'user'            => $user,
                'profiles'        => $this->profiles->listForSelect(),
                'logins'          => $this->logins->forUser($user->id),
                'passwordChanges' => $this->passwordChanges->forUser($user->id),
                'resets'          => $this->resets->forUser($user->id),
            ])
        );
    }
}
