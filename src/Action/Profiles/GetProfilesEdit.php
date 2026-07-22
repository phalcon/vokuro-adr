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

namespace Vokuro\Action\Profiles;

use Phalcon\ADR\Payload\Payload;
use Phalcon\ADR\Responder\Redirect;
use Phalcon\ADR\Responder\RedirectResponder;
use Phalcon\Contracts\ADR\Action;
use Phalcon\Contracts\Http\AttributeRequest;
use Phalcon\Http\Response;
use Phalcon\Http\ResponseInterface;
use Vokuro\Contracts\Repository\ProfileRepository;
use Vokuro\Contracts\Repository\UserRepository;
use Vokuro\Responder\PrivateResponder;

/**
 * Shows the edit form for a profile, with the users assigned to it.
 */
final class GetProfilesEdit implements Action
{
    public function __construct(
        private ProfileRepository $profiles,
        private UserRepository $users,
        private PrivateResponder $view,
        private RedirectResponder $redirect
    ) {
    }

    public function __invoke(AttributeRequest $request): ResponseInterface
    {
        $profile = $this->profiles->findById((int) $request->getAttributes()->get(0));

        if (null === $profile) {
            return ($this->redirect)(
                $request,
                new Response(),
                Payload::found(new Redirect('/profiles'))
            );
        }

        return ($this->view->withTemplate('profiles/edit'))(
            $request,
            new Response(),
            Payload::success([
                'profile' => $profile,
                'users'   => $this->users->byProfile($profile->id),
            ])
        );
    }
}
