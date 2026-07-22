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
 * Creates a profile. The rules the form used to declare live here.
 */
final class CreateProfile
{
    public function __construct(
        private ProfileRepository $profiles
    ) {
    }

    public function __invoke(Input $input): PayloadInterface
    {
        $name = trim((string) $input->get('name'));

        if ('' === $name) {
            return Payload::invalid(['name' => 'The name is required']);
        }

        $id = $this->profiles->add(
            [
                'name'   => $name,
                'active' => 'Y' === $input->get('active') ? 'Y' : 'N',
            ]
        );

        return Payload::created(['id' => $id])
            ->withMessages(['Profile was created successfully']);
    }
}
