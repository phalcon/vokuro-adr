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

namespace Vokuro\Action\Permissions;

use Phalcon\ADR\Payload\Payload;
use Phalcon\Contracts\ADR\Action;
use Phalcon\Contracts\Http\AttributeRequest;
use Phalcon\Http\Response;
use Phalcon\Http\ResponseInterface;
use Vokuro\Application\Acl;
use Vokuro\Contracts\Repository\ProfileRepository;
use Vokuro\Responder\PrivateResponder;

/**
 * Shows the permissions screen with just the profile picker. Choosing a
 * profile posts back to reveal its permissions.
 */
final class GetPermissions implements Action
{
    public function __construct(
        private ProfileRepository $profiles,
        private Acl $acl,
        private PrivateResponder $responder
    ) {
    }

    public function __invoke(AttributeRequest $request): ResponseInterface
    {
        return ($this->responder->withTemplate('permissions/index'))(
            $request,
            new Response(),
            Payload::success([
                'profiles'  => $this->profiles->listForSelect(),
                'resources' => $this->acl->resources(),
                'acl'       => $this->acl,
                'profile'   => null,
                'granted'   => [],
            ])
        );
    }
}
