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

namespace Vokuro\Domain\Collection;

use Phalcon\Support\Collection;
use Vokuro\Domain\Model\Profile;

/**
 * A typed collection of profiles, keyed by id.
 *
 * @extends Collection<string, Profile>
 */
final class ProfileCollection extends Collection
{
    /**
     * @param array<int, Profile> $profiles
     */
    public function __construct(array $profiles = [])
    {
        parent::__construct([], false, false, Profile::class);

        foreach ($profiles as $profile) {
            $this->set((string) $profile->id, $profile);
        }
    }
}
