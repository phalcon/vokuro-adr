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
use Vokuro\Domain\Profiles\UpdateProfile;
use Vokuro\Responder\PrivateResponder;

/**
 * Saves an edited profile.
 */
final class PostProfilesEdit implements Action
{
    public function __construct(
        private UpdateProfile $domain,
        private ProfileRepository $profiles,
        private UserRepository $users,
        private PrivateResponder $view,
        private RedirectResponder $redirect,
        private Security $security
    ) {
    }

    public function __invoke(AttributeRequest $request): ResponseInterface
    {
        $id = (int) $request->getAttributes()->get(0);

        if (false === $this->security->checkToken('csrf', $request->getPost('csrf'))) {
            return $this->form($request, $id, Payload::invalid(['csrf' => 'The form has expired, please try again']));
        }

        $payload = ($this->domain)(
            new Input(['id' => $id] + $request->getPost())
        );

        if (Status::UPDATED === $payload->getStatus() || Status::NOT_FOUND === $payload->getStatus()) {
            return ($this->redirect)(
                $request,
                new Response(),
                Payload::found(new Redirect('/profiles'))
            );
        }

        return $this->form($request, $id, $payload);
    }

    private function form(
        AttributeRequest $request,
        int $id,
        PayloadInterface $payload
    ): ResponseInterface {
        $profile = $this->profiles->findById($id);

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
            $payload->withResult([
                'profile' => $profile,
                'users'   => $this->users->byProfile($id),
            ])
        );
    }
}
