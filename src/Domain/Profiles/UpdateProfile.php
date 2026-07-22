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

namespace Vokuro\Domain\Profiles;

use Phalcon\ADR\Input\Input;
use Phalcon\ADR\Payload\Payload;
use Phalcon\Contracts\ADR\Payload\Payload as PayloadInterface;
use Vokuro\Contracts\Repository\ProfileRepository;

/**
 * Saves changes to an existing profile.
 */
final class UpdateProfile
{
    public function __construct(
        private ProfileRepository $profiles
    ) {
    }

    public function __invoke(Input $input): PayloadInterface
    {
        $id = (int) $input->get('id');

        if (null === $this->profiles->findById($id)) {
            return Payload::notFound(['Profile was not found.']);
        }

        $name = trim((string) $input->get('name'));

        if ('' === $name) {
            return Payload::invalid(['name' => 'The name is required']);
        }

        $this->profiles->update(
            $id,
            [
                'name'   => $name,
                'active' => 'Y' === $input->get('active') ? 'Y' : 'N',
            ]
        );

        return Payload::updated(['id' => $id])
            ->withMessages(['Profile was updated successfully']);
    }
}
