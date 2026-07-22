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
use Vokuro\Domain\Model\User;

/**
 * A typed collection of users, keyed by id.
 *
 * `Phalcon\Support\Collection` is a string keyed bag, so the id is the key.
 * The `User::class` guard makes it reject anything that is not a user at the
 * moment it is added.
 *
 * @extends Collection<string, User>
 */
final class UserCollection extends Collection
{
    /**
     * @param array<int, User> $users
     */
    public function __construct(array $users = [])
    {
        parent::__construct([], false, false, User::class);

        /**
         * Set one by one rather than passing the array to the parent: a numeric
         * string key becomes an integer the moment it lands in a PHP array, and
         * the parent's constructor rejects an integer key.
         */
        foreach ($users as $user) {
            $this->set((string) $user->id, $user);
        }
    }
}
