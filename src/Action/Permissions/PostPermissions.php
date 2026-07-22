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

use Phalcon\ADR\Input\Input;
use Phalcon\ADR\Payload\Payload;
use Phalcon\Contracts\ADR\Action;
use Phalcon\Contracts\Http\AttributeRequest;
use Phalcon\Encryption\Security;
use Phalcon\Http\Response;
use Phalcon\Http\ResponseInterface;
use Vokuro\Application\Acl;
use Vokuro\Contracts\Repository\PermissionRepository;
use Vokuro\Contracts\Repository\ProfileRepository;
use Vokuro\Domain\Permissions\SavePermissions;
use Vokuro\Responder\PrivateResponder;

/**
 * Reveals or saves a profile's permissions. Picking a profile shows its
 * current grants; submitting the checkboxes replaces them.
 */
final class PostPermissions implements Action
{
    public function __construct(
        private SavePermissions $domain,
        private ProfileRepository $profiles,
        private PermissionRepository $permissions,
        private Acl $acl,
        private PrivateResponder $responder,
        private Security $security
    ) {
    }

    public function __invoke(AttributeRequest $request): ResponseInterface
    {
        $messages = [];

        if (false === $this->security->checkToken('csrf', $request->getPost('csrf'))) {
            return $this->render($request, null, ['The form has expired, please try again']);
        }

        $profile = $this->profiles->findById((int) $request->getPost('profileId'));

        if (null === $profile) {
            return $this->render($request, null, []);
        }

        if (null !== $request->getPost('submit')) {
            $payload  = ($this->domain)(Input::fromRequest($request));
            $messages = (array) $payload->getMessages();
        }

        return $this->render($request, $profile->id, $messages);
    }

    /**
     * @param array<array-key, mixed> $messages
     */
    private function render(
        AttributeRequest $request,
        ?int $profileId,
        array $messages
    ): ResponseInterface {
        return ($this->responder->withTemplate('permissions/index'))(
            $request,
            new Response(),
            Payload::success([
                'profiles'  => $this->profiles->listForSelect(),
                'resources' => $this->acl->resources(),
                'acl'       => $this->acl,
                'profile'   => $profileId,
                'granted'   => null === $profileId ? [] : $this->permissions->grantedTo($profileId),
            ])->withMessages($messages)
        );
    }
}
