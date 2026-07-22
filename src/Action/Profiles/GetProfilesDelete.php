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

/**
 * Removes a profile and returns to the list.
 */
final class GetProfilesDelete implements Action
{
    public function __construct(
        private ProfileRepository $profiles,
        private RedirectResponder $redirect
    ) {
    }

    public function __invoke(AttributeRequest $request): ResponseInterface
    {
        $id = (int) $request->getAttributes()->get(0);

        if (null !== $this->profiles->findById($id)) {
            $this->profiles->delete($id);
        }

        return ($this->redirect)(
            $request,
            new Response(),
            Payload::found(new Redirect('/profiles'))
        );
    }
}
