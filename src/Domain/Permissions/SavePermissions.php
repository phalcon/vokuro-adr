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

namespace Vokuro\Domain\Permissions;

use Phalcon\ADR\Input\Input;
use Phalcon\ADR\Payload\Payload;
use Phalcon\Contracts\ADR\Payload\Payload as PayloadInterface;
use Vokuro\Contracts\Repository\PermissionRepository;
use Vokuro\Contracts\Repository\ProfileRepository;

/**
 * Sets a profile's permissions to exactly the pairs that were checked.
 */
final class SavePermissions
{
    public function __construct(
        private ProfileRepository $profiles,
        private PermissionRepository $permissions
    ) {
    }

    public function __invoke(Input $input): PayloadInterface
    {
        $profileId = (int) $input->get('profileId');

        if (null === $this->profiles->findById($profileId)) {
            return Payload::notFound(['Profile was not found.']);
        }

        /** @var list<string> $pairs */
        $pairs = (array) $input->get('permissions', []);

        $this->permissions->replaceForProfile($profileId, $pairs);

        return Payload::updated(['profileId' => $profileId])
            ->withMessages(['Permissions were updated successfully']);
    }
}
